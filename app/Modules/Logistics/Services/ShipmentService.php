<?php

namespace App\Modules\Logistics\Services;

use App\Modules\Logistics\Models\Shipment;
use App\Modules\Logistics\Models\StockMove;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    /**
     * Create a new shipment
     */
    public function create(array $data): Shipment
    {
        $shipment = new Shipment();
        $shipment->shipment_number = Shipment::generateShipmentNumber();
        $shipment->fill($data);

        // Calculate volume if dimensions provided
        if ($shipment->length_cm && $shipment->width_cm && $shipment->height_cm) {
            $shipment->calculateVolume();
        }

        $shipment->save();

        return $shipment;
    }

    /**
     * Get all shipments for tenant
     */
    public function getShipments(int $tenantId, array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = Shipment::where('tenant_id', $tenantId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['courier'])) {
            $query->where('courier', $filters['courier']);
        }

        if (isset($filters['order_type'])) {
            $query->where('order_type', $filters['order_type']);
        }

        if (isset($filters['tracking_number'])) {
            $query->where('tracking_number', 'like', "%{$filters['tracking_number']}%");
        }

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')
                     ->paginate(50);
    }

    /**
     * Get shipment by ID
     */
    public function getShipment(int $shipmentId): ?Shipment
    {
        return Shipment::find($shipmentId);
    }

    /**
     * Update shipment status
     */
    public function updateStatus(int $shipmentId, string $status, array $extraData = []): Shipment
    {
        $shipment = Shipment::findOrFail($shipmentId);

        // Validate status transition
        $this->validateStatusTransition($shipment->status, $status);

        $updateData = ['status' => $status];

        // Handle delivered status
        if ($status === Shipment::STATUS_DELIVERED) {
            $updateData['delivered_at'] = now();
            $updateData['signed_by'] = $extraData['signed_by'] ?? null;
            $updateData['proof_of_delivery'] = $extraData['proof_of_delivery'] ?? null;
        }

        $shipment->update($updateData);

        return $shipment;
    }

    /**
     * Generate shipping label
     */
    public function generateLabel(int $shipmentId): array
    {
        $shipment = Shipment::findOrFail($shipmentId);

        // Integrate with courier API
        $labelData = $this->callCourierApi('generateLabel', [
            'shipment' => $shipment,
            'courier' => $shipment->courier,
        ]);

        $shipment->update([
            'status' => Shipment::STATUS_LABEL_GENERATED,
            'tracking_number' => $labelData['tracking_number'],
            'labels' => $labelData['labels'],
        ]);

        return $labelData;
    }

    /**
     * Track shipment via courier API
     */
    public function trackShipment(int $shipmentId): array
    {
        $shipment = Shipment::findOrFail($shipmentId);

        if (!$shipment->tracking_number) {
            throw new \RuntimeException('No tracking number available.');
        }

        return $this->callCourierApi('trackShipment', [
            'tracking_number' => $shipment->tracking_number,
            'courier' => $shipment->courier,
        ]);
    }

    /**
     * Create shipment from order
     */
    public function createFromOrder(int $orderId, string $orderType, int $fromWarehouseId): Shipment
    {
        return DB::transaction(function () use ($orderId, $orderType, $fromWarehouseId) {
            // Get order details (placeholder - integrate with B-Invoicing/B-SCM)
            $order = $this->getOrderDetails($orderId, $orderType);

            // Create shipment record
            $shipment = $this->create([
                'tenant_id' => $order['tenant_id'],
                'order_type' => $orderType,
                'order_id' => $orderId,
                'from_warehouse_id' => $fromWarehouseId,
                'recipient_name' => $order['recipient_name'],
                'recipient_phone' => $order['recipient_phone'],
                'shipping_address' => $order['shipping_address'],
                'shipping_city' => $order['shipping_city'],
                'shipping_state' => $order['shipping_state'],
                'shipping_postal_code' => $order['shipping_postal_code'],
                'weight_kg' => $order['total_weight'],
                'declared_value' => $order['total_value'],
                'currency' => $order['currency'],
                'number_of_packages' => $order['package_count'] ?? 1,
                'status' => Shipment::STATUS_CREATED,
            ]);

            // Create stock moves for each item
            foreach ($order['items'] as $item) {
                StockMove::create([
                    'tenant_id' => $order['tenant_id'],
                    'warehouse_id' => $fromWarehouseId,
                    'product_id' => $item['product_id'],
                    'quantity' => -$item['quantity'],
                    'unit_cost' => $item['cost'],
                    'move_type' => StockMove::TYPE_OUTGOING,
                    'reference_type' => 'shipment',
                    'reference_id' => $shipment->id,
                    'moved_by' => $order['created_by'],
                    'status' => StockMove::STATUS_COMPLETED,
                    'moved_at' => now(),
                ]);
            }

            return $shipment;
        });
    }

    /**
     * Get shipment statistics
     */
    public function getStatistics(int $tenantId, array $filters = []): array
    {
        $query = Shipment::where('tenant_id', $tenantId);

        if (isset($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        $totalShipments = (clone $query)->count();
        $deliveredShipments = (clone $query)->where('status', Shipment::STATUS_DELIVERED)->count();
        $inTransitShipments = (clone $query)->whereIn('status', [
            Shipment::STATUS_PICKED_UP,
            Shipment::STATUS_IN_TRANSIT,
            Shipment::STATUS_OUT_FOR_DELIVERY,
        ])->count();

        $totalShippingCost = (clone $query)->sum('shipping_cost');
        $avgShippingCost = $totalShipments > 0 ? ($totalShippingCost / $totalShipments) : 0;

        $byCourier = (clone $query)->selectRaw('courier, COUNT(*) as count, SUM(shipping_cost) as total_cost')
            ->groupBy('courier')
            ->get();

        $byStatus = (clone $query)->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        return [
            'total_shipments' => $totalShipments,
            'delivered_shipments' => $deliveredShipments,
            'in_transit_shipments' => $inTransitShipments,
            'delivery_rate' => $totalShipments > 0 ? ($deliveredShipments / $totalShipments * 100) : 0,
            'total_shipping_cost' => $totalShippingCost,
            'avg_shipping_cost' => round($avgShippingCost, 2),
            'by_courier' => $byCourier,
            'by_status' => $byStatus,
        ];
    }

    /**
     * Validate status transition
     */
    private function validateStatusTransition(string $from, string $to): void
    {
        $validTransitions = [
            Shipment::STATUS_CREATED => [
                Shipment::STATUS_LABEL_GENERATED,
                Shipment::STATUS_CANCELLED,
            ],
            Shipment::STATUS_LABEL_GENERATED => [
                Shipment::STATUS_PICKED_UP,
                Shipment::STATUS_CANCELLED,
            ],
            Shipment::STATUS_PICKED_UP => [
                Shipment::STATUS_IN_TRANSIT,
                Shipment::STATUS_FAILED,
            ],
            Shipment::STATUS_IN_TRANSIT => [
                Shipment::STATUS_OUT_FOR_DELIVERY,
                Shipment::STATUS_FAILED,
            ],
            Shipment::STATUS_OUT_FOR_DELIVERY => [
                Shipment::STATUS_DELIVERED,
                Shipment::STATUS_FAILED,
                Shipment::STATUS_RETURNED,
            ],
            Shipment::STATUS_FAILED => [
                Shipment::STATUS_RETURNED,
            ],
        ];

        if (isset($validTransitions[$from]) && !in_array($to, $validTransitions[$from])) {
            throw new \RuntimeException(
                "Invalid status transition from {$from} to {$to}"
            );
        }
    }

    /**
     * Call courier API (adapter pattern)
     */
    private function callCourierApi(string $action, array $params): array
    {
        $courier = $params['courier'] ?? config('logistics.shipment.default_courier');
        unset($params['courier']);

        // Adapter pattern - implement per courier
        switch ($courier) {
            case 'dhl':
                return $this->callDhlApi($action, $params);
            case 'jandeln':
                return $this->callJandelnApi($action, $params);
            case 'gdex':
                return $this->callGdexApi($action, $params);
            default:
                throw new \RuntimeException("Courier {$courier} not supported");
        }
    }

    private function callDhlApi(string $action, array $params): array
    {
        // DHL API integration placeholder
        return ['tracking_number' => 'DHL' . time(), 'labels' => []];
    }

    private function callJandelnApi(string $action, array $params): array
    {
        // J&N API integration placeholder
        return ['tracking_number' => 'JN' . time(), 'labels' => []];
    }

    private function callGdexApi(string $action, array $params): array
    {
        // GDEX API integration placeholder
        return ['tracking_number' => 'GD' . time(), 'labels' => []];
    }

    /**
     * Get order details (placeholder for integration)
     */
    private function getOrderDetails(int $orderId, string $orderType): array
    {
        // Placeholder - integrate with B-Invoicing or B-SCM
        return [
            'tenant_id' => 1,
            'recipient_name' => 'Customer Name',
            'recipient_phone' => '+60123456789',
            'shipping_address' => 'Address Line',
            'shipping_city' => 'City',
            'shipping_state' => 'State',
            'shipping_postal_code' => '12345',
            'total_weight' => 1.0,
            'total_value' => 100.00,
            'currency' => 'MYR',
            'package_count' => 1,
            'items' => [],
            'created_by' => 1,
        ];
    }
}
