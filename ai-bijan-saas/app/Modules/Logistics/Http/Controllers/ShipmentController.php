<?php

namespace App\Modules\Logistics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Logistics\Services\ShipmentService;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    protected $shipmentService;

    public function __construct(ShipmentService $shipmentService)
    {
        $this->shipmentService = $shipmentService;
    }

    /**
     * List all shipments
     */
    public function index(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();

        $shipments = $this->shipmentService->getShipments($tenantId, [
            'status' => $request->query('status'),
            'courier' => $request->query('courier'),
            'order_type' => $request->query('order_type'),
            'tracking_number' => $request->query('tracking_number'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $shipments,
        ]);
    }

    /**
     * Get shipment detail
     */
    public function show(int $id, Request $request)
    {
        $shipment = $this->shipmentService->getShipment($id);

        if (!$shipment || $shipment->tenant_id !== app(TenantContext::class)->id()) {
            return response()->json(['message' => 'Shipment not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $shipment,
        ]);
    }

    /**
     * Create new shipment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_type' => 'required|in:sales,purchase,transfer',
            'order_id' => 'required|integer',
            'from_warehouse_id' => 'required|exists:logistics_warehouses,id',
            'to_warehouse_id' => 'nullable|exists:logistics_warehouses,id',
            'recipient_name' => 'required|string|max:255',
            'recipient_phone' => 'required|string|max:30',
            'recipient_email' => 'nullable|email|max:255',
            'shipping_address' => 'required|string',
            'shipping_city' => 'required|string|max:100',
            'shipping_state' => 'required|string|max:100',
            'shipping_postal_code' => 'required|string|max:20',
            'shipping_country' => 'nullable|string|max:100',
            'courier' => 'nullable|in:dhl,ups,fedex,jandeln,gdex,poslaju,ninja',
            'service_type' => 'nullable|string|max:50',
            'weight_kg' => 'nullable|numeric|min:0',
            'length_cm' => 'nullable|numeric|min:0',
            'width_cm' => 'nullable|numeric|min:0',
            'height_cm' => 'nullable|numeric|min:0',
            'package_type' => 'nullable|in:parcel,envelope,pallet,custom',
            'number_of_packages' => 'nullable|integer|min:1',
            'declared_value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = app(TenantContext::class)->id();
        $validated['status'] = \App\Modules\Logistics\Models\Shipment::STATUS_CREATED;

        try {
            $shipment = $this->shipmentService->create($validated);

            return response()->json([
                'success' => true,
                'data' => $shipment,
                'message' => 'Shipment created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update shipment status
     */
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:created,label_generated,picked_up,in_transit,out_for_delivery,delivered,failed,returned,cancelled',
            'signed_by' => 'nullable|string|max:255',
            'proof_of_delivery' => 'nullable|string',
        ]);

        $status = $validated['status'];
        unset($validated['status']);

        try {
            $shipment = $this->shipmentService->updateStatus($id, $status, $validated);

            return response()->json([
                'success' => true,
                'data' => $shipment,
                'message' => 'Shipment status updated',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Generate shipping label
     */
    public function generateLabel(int $id, Request $request)
    {
        try {
            $labelData = $this->shipmentService->generateLabel($id);

            return response()->json([
                'success' => true,
                'data' => $labelData,
                'message' => 'Shipping label generated',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Track shipment
     */
    public function track(int $id, Request $request)
    {
        try {
            $trackingInfo = $this->shipmentService->trackShipment($id);

            return response()->json([
                'success' => true,
                'data' => $trackingInfo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Create shipment from order
     */
    public function createFromOrder(Request $request)
    {
        $validated = $request->validate([
            'order_type' => 'required|in:sales,purchase,transfer',
            'order_id' => 'required|integer',
            'from_warehouse_id' => 'required|exists:logistics_warehouses,id',
        ]);

        try {
            $shipment = $this->shipmentService->createFromOrder(
                $validated['order_id'],
                $validated['order_type'],
                $validated['from_warehouse_id']
            );

            return response()->json([
                'success' => true,
                'data' => $shipment,
                'message' => 'Shipment created from order',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get shipment statistics
     */
    public function getStatistics(Request $request)
    {
        $tenantId = app(TenantContext::class)->id();

        $stats = $this->shipmentService->getStatistics($tenantId, [
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ]);

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
