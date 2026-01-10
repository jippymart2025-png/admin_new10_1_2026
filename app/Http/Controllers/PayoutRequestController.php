<?php

namespace App\Http\Controllers;

use App\Models\AppUser;
use App\Models\Currency;
use App\Models\DriverPayout;
use App\Models\Payout;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayoutRequestController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id = '')
    {
        $driver = null;

        if (!empty($id)) {
            $driver = AppUser::where('role', 'driver')
                ->where(function ($query) use ($id) {
                    $query->where('id', $id)
                        ->orWhere('firebase_id', $id)
                        ->orWhere('_id', $id);
                })
                ->select('id', 'firebase_id', '_id', 'firstName', 'lastName')
                ->first();
        }

        return view('payoutRequests.drivers.index', [
            'id' => $id,
            'driver' => $driver,
        ]);
    }

    public function restaurant($id = '')
    {
        $vendor = null;

        if (!empty($id)) {
            $vendor = Vendor::select('id', 'title', 'author')->find($id);
        }

        return view('payoutRequests.restaurants.index', [
            'id' => $id,
            'vendor' => $vendor,
        ]);
    }

    /**
     * Helper to fetch active currency configuration.
     */
    protected function getCurrencyFormatting(): array
    {
        $currency = Currency::where('isActive', true)->first();

        return [
            'symbol' => $currency->symbol ?? '$',
            'symbol_at_right' => (bool) ($currency->symbolAtRight ?? false),
            'decimal_digits' => (int) ($currency->decimal_degits ?? 2),
        ];
    }

    protected function formatAmount($amount, array $currency): string
    {
        $amount = is_numeric($amount) ? (float) $amount : 0.0;
        $formatted = number_format($amount, $currency['decimal_digits'], '.', '');

        return $currency['symbol_at_right']
            ? $formatted . $currency['symbol']
            : $currency['symbol'] . $formatted;
    }

    protected function formatPaidDate(?string $paidDate): string
    {
        if (empty($paidDate)) {
            return '--';
        }

        try {
            $clean = trim($paidDate, '"');
            $date = Carbon::parse($clean);
            return $date->format('D M d Y g:i:s A');
        } catch (\Throwable $e) {
            return $paidDate;
        }
    }

    protected function buildStatusBadge(?string $status): string
    {
        $status = $status ?? '';

        switch ($status) {
            case 'Pending':
            case 'In Process':
                $class = 'order_placed';
                break;
            case 'Reject':
            case 'Failed':
                $class = 'order_rejected';
                break;
            case 'Success':
                $class = 'order_completed';
                break;
            default:
                $class = '';
        }

        if (empty($class)) {
            return e($status);
        }

        return '<span class="' . $class . '"><span>' . e($status) . '</span></span>';
    }

    /**
     * Get restaurant payout requests data (Pending status)
     */
    public function getRestaurantPayoutRequestsData(Request $request)
    {
        try {
            $start = (int)$request->input('start', 0);
            $length = (int)$request->input('length', 10);
            $searchValue = trim(strtolower($request->input('search.value', '')));
            $orderColumnIndex = (int)$request->input('order.0.column', 0);
            $orderDirection = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
            $vendorId = $request->input('vendor_id', '');

            $currency = $this->getCurrencyFormatting();

            $query = Payout::query()
                ->leftJoin('vendors', 'vendors.id', '=', 'payouts.vendorID')
                ->select('payouts.*', 'vendors.title as vendor_title', 'vendors.author as vendor_author')
                ->where('payouts.paymentStatus', 'Pending');

            if (!empty($vendorId)) {
                $query->where('payouts.vendorID', $vendorId);
            }

            $orderableColumns = !empty($vendorId)
                ? ['', 'amount', 'note', 'paidDate', 'paymentStatus', 'withdrawMethod', 'actions']
                : ['', 'vendor', 'amount', 'note', 'paidDate', 'paymentStatus', 'withdrawMethod', 'actions'];

            $fieldMap = [
                'vendor' => 'vendors.title',
                'amount' => 'payouts.amount',
                'note' => 'payouts.note',
                'paidDate' => 'payouts.paidDate',
                'paymentStatus' => 'payouts.paymentStatus',
                'withdrawMethod' => 'payouts.withdrawMethod',
            ];

            $requestedColumn = $orderableColumns[$orderColumnIndex] ?? 'paidDate';
            $orderByField = $fieldMap[$requestedColumn] ?? 'payouts.paidDate';

            if ($searchValue !== '') {
                $query->where(function ($q) use ($searchValue, $vendorId) {
                    $q->where('payouts.note', 'like', "%{$searchValue}%")
                        ->orWhere('payouts.paymentStatus', 'like', "%{$searchValue}%")
                        ->orWhere('payouts.withdrawMethod', 'like', "%{$searchValue}%")
                        ->orWhere('payouts.amount', 'like', "%{$searchValue}%");

                    if (empty($vendorId)) {
                        $q->orWhere('vendors.title', 'like', "%{$searchValue}%");
                    }
                });
            }

            $totalRecords = (clone $query)->count();

            $payouts = $query->orderBy($orderByField, $orderDirection)
                ->skip($start)
                ->take($length)
                ->get();

            $data = [];

            foreach ($payouts as $payout) {
                $checkbox = '<input type="checkbox" class="is_open" dataId="' . e($payout->id) . '">';

                $vendorCell = '';
                if (empty($vendorId)) {
                    if (!empty($payout->vendorID)) {
                        $vendorRoute = route('restaurants.view', $payout->vendorID);
                        $vendorCell = '<a href="' . $vendorRoute . '">' . e($payout->vendor_title ?? 'Unknown') . '</a>';
                    } else {
                        $vendorCell = e($payout->vendor_title ?? 'Unknown');
                    }
                }

                $amount = $this->formatAmount($payout->amount, $currency);
                $note = e($payout->note ?? '');
                $paidDate = $this->formatPaidDate($payout->paidDate);
                $status = $this->buildStatusBadge($payout->paymentStatus);
                $withdrawMethod = $payout->withdrawMethod === 'bank'
                    ? 'Bank Transfer'
                    : ucfirst($payout->withdrawMethod ?? '');

                $data[] = [
                    'checkbox' => $checkbox,
                    'vendor' => $vendorCell,
                    'amount' => $amount,
                    'note' => $note,
                    'paidDate' => $paidDate,
                    'status' => $status,
                    'withdrawMethod' => e($withdrawMethod),
                    'actions' => '-',
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($request->input('draw', 0)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error fetching payout requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get driver payout requests data (Pending status)
     */
    public function getDriverPayoutRequestsData(Request $request)
    {
        try {
            $start = (int) $request->input('start', 0);
            $length = (int) $request->input('length', 10);
            $searchValue = trim(strtolower($request->input('search.value', '')));
            $orderColumnIndex = (int) $request->input('order.0.column', 0);
            $orderDirection = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';
            $driverId = $request->input('driver_id', '');

            $currency = $this->getCurrencyFormatting();

            $query = DriverPayout::query()
                ->leftJoin('users', function ($join) {
                    $join->on('driver_payouts.driverID', '=', 'users.id')
                        ->orOn('driver_payouts.driverID', '=', 'users.firebase_id')
                        ->orOn('driver_payouts.driverID', '=', 'users._id');
                })
                ->select('driver_payouts.*', 'users.firstName as driver_first_name', 'users.lastName as driver_last_name')
                ->where('driver_payouts.paymentStatus', 'Pending');

            if (!empty($driverId)) {
                $query->where(function ($q) use ($driverId) {
                    $q->where('driver_payouts.driverID', $driverId)
                        ->orWhere('users.id', $driverId)
                        ->orWhere('users.firebase_id', $driverId)
                        ->orWhere('users._id', $driverId);
                });
            }

            $orderableColumns = !empty($driverId)
                ? ['', 'amount', 'note', 'paidDate', 'paymentStatus', 'withdrawMethod', 'actions']
                : ['', 'driver', 'amount', 'note', 'paidDate', 'paymentStatus', 'withdrawMethod', 'actions'];

            $fieldMap = [
                'driver' => 'users.firstName',
                'amount' => 'driver_payouts.amount',
                'note' => 'driver_payouts.note',
                'paidDate' => 'driver_payouts.paidDate',
                'paymentStatus' => 'driver_payouts.paymentStatus',
                'withdrawMethod' => 'driver_payouts.withdrawMethod',
            ];

            $requestedColumn = $orderableColumns[$orderColumnIndex] ?? 'paidDate';
            $orderByField = $fieldMap[$requestedColumn] ?? 'driver_payouts.paidDate';

            if ($searchValue !== '') {
                $query->where(function ($q) use ($searchValue, $driverId) {
                    $q->where('driver_payouts.note', 'like', "%{$searchValue}%")
                      ->orWhere('driver_payouts.paymentStatus', 'like', "%{$searchValue}%")
                      ->orWhere('driver_payouts.withdrawMethod', 'like', "%{$searchValue}%")
                      ->orWhere('driver_payouts.amount', 'like', "%{$searchValue}%");

                    if (empty($driverId)) {
                        $q->orWhere(DB::raw("CONCAT(IFNULL(users.firstName,''),' ',IFNULL(users.lastName,''))"), 'like', "%{$searchValue}%");
                    }
                });
            }

            $totalRecords = (clone $query)->count();

            $payouts = $query->orderBy($orderByField, $orderDirection)
                ->skip($start)
                ->take($length)
                ->get();

            $data = [];

            foreach ($payouts as $payout) {
                $checkbox = '<input type="checkbox" class="is_open" dataId="' . e($payout->id) . '">';

                $driverName = trim(($payout->driver_first_name ?? '') . ' ' . ($payout->driver_last_name ?? ''));
                if (empty($driverName)) {
                    $driverName = 'Unknown';
                }

                $driverCell = $driverName;
                if (empty($driverId) && !empty($payout->driverID)) {
                    $driverRoute = route('drivers.view', $payout->driverID);
                    $driverCell = '<a href="' . $driverRoute . '">' . e($driverName) . '</a>';
                }

                $amount = $this->formatAmount($payout->amount, $currency);
                $note = e($payout->note ?? '');
                $paidDate = $this->formatPaidDate($payout->paidDate);
                $status = $this->buildStatusBadge($payout->paymentStatus);
                $withdrawMethod = $payout->withdrawMethod === 'bank'
                    ? 'Bank Transfer'
                    : ucfirst($payout->withdrawMethod ?? '');

                $data[] = [
                    'checkbox' => $checkbox,
                    'driver' => $driverCell,
                    'amount' => $amount,
                    'note' => $note,
                    'paidDate' => $paidDate,
                    'status' => $status,
                    'withdrawMethod' => e($withdrawMethod),
                    'actions' => '-',
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'draw' => intval($request->input('draw', 0)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error fetching driver payout requests: ' . $e->getMessage(),
            ], 500);
        }
    }
}
