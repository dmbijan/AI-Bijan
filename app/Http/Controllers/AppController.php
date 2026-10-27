<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class AppController extends Controller
{
    /**
     * Halaman papan pemuka utama.
     */
    public function dashboard(): View
    {
        return view('app.dashboard');
    }

    /**
     * Halaman modul — memetakan slug kepada paparan.
     */
    public function module(string $module): View
    {
        $map = [
            'ai-agents'       => 'ai-agents',
            'team-ai'         => 'team-ai',
            'openbot'         => 'openbot',
            'command-centre'  => 'command-centre',
            'prospects'       => 'prospects',
            'blast'           => 'blast',
            'boardroom'       => 'boardroom',
            'personal-ai'     => 'personal-ai',
            'flows'           => 'flows',
            'ai-gateway'      => 'ai-gateway',
            'pejabat'         => 'pejabat',
            'pos'             => 'pos',
            'crm'             => 'crm',
            'invoicing'       => 'invoicing',
            'marketplace'     => 'marketplace',
            'booking'         => 'booking',
            'omnichannel'     => 'omnichannel',
            'helpdesk'        => 'helpdesk',
            'loyalty'         => 'loyalty',
            'affiliate'       => 'affiliate',
            'link'            => 'link',
            'marketing'       => 'marketing',
            'site'            => 'site',
            'inventory'       => 'inventory',
            'cogs'            => 'cogs',
            'manufacturing'   => 'manufacturing',
            'logistics'       => 'logistics',
            'scm'             => 'scm',
            'assets'          => 'assets',
            'finance'         => 'finance',
            'payroll'         => 'payroll',
            'hrms'            => 'hrms',
            'projects'        => 'projects',
            'sign-docs'       => 'sign-docs',
            'analytics'       => 'analytics',
            'brainhub'        => 'brainhub',
            'industry'        => 'industry',
        ];

        abort_unless(isset($map[$module]), 404);

        $view = "app.{$map[$module]}";

        if (!view()->exists($view)) {
            $view = 'app.placeholder';
        }

        return view($view, ['module' => $module]);
    }
}
