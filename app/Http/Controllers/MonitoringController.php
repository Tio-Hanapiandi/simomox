<?php

namespace App\Http\Controllers;

use App\Services\ProxmoxService;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    protected $proxmoxService;

    public function __construct(ProxmoxService $proxmoxService)
    {
        $this->proxmoxService = $proxmoxService;
    }

    public function index()
    {
        $nodeStatus = $this->proxmoxService->getNodeStatus('pve24'); // Ganti 'pve' dengan nama node Anda
        
        // Kirim data status node ke view
        return view('index', compact('nodeStatus'));
    }
    public function getNodeStatusAjax($nodeName)
    {
        $nodeStatus = $this->proxmoxService->getNodeStatus($nodeName);
        return response()->json(['data' => $nodeStatus]);
    }
}
