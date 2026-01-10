<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Firestore\FirestoreClient;
use Illuminate\Support\Facades\Log;

class ZoneBonusController extends Controller
{
    protected $firestore;

    public function __construct()
    {
        $this->middleware('auth');
        $this->firestore = new FirestoreClient([
            'projectId' => config('firestore.project_id'),
            'keyFilePath' => config('firestore.credentials'),
        ]);
    }

    /**
     * Display zone bonus settings page
     */
    public function index()
    {
        return view('settings.zone_bonus_settings');
    }

    /**
     * Get zone bonus settings via API
     */
    public function getZoneBonusSettings()
    {
        try {
            $query = $this->firestore->collection('zone_bonus_settings')
                ->orderBy('zoneName', 'asc');
            
            $documents = $query->documents();
            $settings = [];
            
            foreach ($documents as $document) {
                $data = $document->data();
                $data['id'] = $document->id();
                $settings[] = $data;
            }
            
            return response()->json([
                'status' => true,
                'data' => $settings
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting zone bonus settings: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving zone bonus settings'
            ], 500);
        }
    }

    /**
     * Create new zone bonus setting
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'zoneId' => 'required|string',
                'requiredOrdersForBonus' => 'required|integer|min:1|max:50',
                'bonusAmount' => 'required|numeric|min:1',
                'isActive' => 'boolean'
            ]);

            // Check if zone exists
            $zoneDoc = $this->firestore->collection('zone')->document($request->zoneId)->snapshot();
            if (!$zoneDoc->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Zone not found'
                ], 404);
            }

            $zoneData = $zoneDoc->data();

            // Check if bonus setting already exists for this zone
            $existingQuery = $this->firestore->collection('zone_bonus_settings')
                ->where('zoneId', '=', $request->zoneId);
            
            $existingDocs = $existingQuery->documents();
            if (iterator_count($existingDocs) > 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Bonus settings already exist for this zone'
                ], 400);
            }

            // Create new bonus setting
            $bonusId = $this->firestore->collection('temp')->document()->id();
            $bonusData = [
                'id' => $bonusId,
                'zoneId' => $request->zoneId,
                'zoneName' => $zoneData['name'],
                'requiredOrdersForBonus' => (int) $request->requiredOrdersForBonus,
                'bonusAmount' => (float) $request->bonusAmount,
                'isActive' => $request->has('isActive') ? (bool) $request->isActive : true,
                'createdAt' => new \DateTime(),
                'updatedAt' => new \DateTime()
            ];

            $this->firestore->collection('zone_bonus_settings')->document($bonusId)->set($bonusData);

            Log::info('Zone bonus setting created', [
                'zoneId' => $request->zoneId,
                'zoneName' => $zoneData['name'],
                'requiredOrders' => $request->requiredOrdersForBonus,
                'bonusAmount' => $request->bonusAmount
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Zone bonus setting created successfully',
                'data' => $bonusData
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating zone bonus setting: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error creating zone bonus setting'
            ], 500);
        }
    }

    /**
     * Update zone bonus setting
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'requiredOrdersForBonus' => 'required|integer|min:1|max:50',
                'bonusAmount' => 'required|numeric|min:1',
                'isActive' => 'boolean'
            ]);

            // Check if bonus setting exists
            $bonusDoc = $this->firestore->collection('zone_bonus_settings')->document($id)->snapshot();
            if (!$bonusDoc->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Zone bonus setting not found'
                ], 404);
            }

            // Update bonus setting
            $updateData = [
                'requiredOrdersForBonus' => (int) $request->requiredOrdersForBonus,
                'bonusAmount' => (float) $request->bonusAmount,
                'isActive' => $request->has('isActive') ? (bool) $request->isActive : true,
                'updatedAt' => new \DateTime()
            ];

            $this->firestore->collection('zone_bonus_settings')->document($id)->update($updateData);

            Log::info('Zone bonus setting updated', [
                'id' => $id,
                'requiredOrders' => $request->requiredOrdersForBonus,
                'bonusAmount' => $request->bonusAmount
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Zone bonus setting updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating zone bonus setting: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error updating zone bonus setting'
            ], 500);
        }
    }

    /**
     * Delete zone bonus setting
     */
    public function destroy($id)
    {
        try {
            // Check if bonus setting exists
            $bonusDoc = $this->firestore->collection('zone_bonus_settings')->document($id)->snapshot();
            if (!$bonusDoc->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Zone bonus setting not found'
                ], 404);
            }

            $bonusData = $bonusDoc->data();

            // Delete bonus setting
            $this->firestore->collection('zone_bonus_settings')->document($id)->delete();

            Log::info('Zone bonus setting deleted', [
                'id' => $id,
                'zoneName' => $bonusData['zoneName'] ?? 'Unknown'
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Zone bonus setting deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting zone bonus setting: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error deleting zone bonus setting'
            ], 500);
        }
    }

    /**
     * Get zone bonus setting for specific zone
     */
    public function getZoneBonusSetting($zoneId)
    {
        try {
            $query = $this->firestore->collection('zone_bonus_settings')
                ->where('zoneId', '=', $zoneId)
                ->where('isActive', '=', true);
            
            $documents = $query->documents();
            
            foreach ($documents as $document) {
                $data = $document->data();
                $data['id'] = $document->id();
                return response()->json([
                    'status' => true,
                    'data' => $data
                ]);
            }
            
            return response()->json([
                'status' => false,
                'message' => 'No active bonus setting found for this zone'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error getting zone bonus setting: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving zone bonus setting'
            ], 500);
        }
    }

    /**
     * Get all zones for dropdown
     */
    public function getZones()
    {
        try {
            $query = $this->firestore->collection('zone')
                ->where('publish', '=', true)
                ->orderBy('name', 'asc');
            
            $documents = $query->documents();
            $zones = [];
            
            foreach ($documents as $document) {
                $data = $document->data();
                $data['id'] = $document->id();
                $zones[] = $data;
            }
            
            return response()->json([
                'status' => true,
                'data' => $zones
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting zones: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving zones'
            ], 500);
        }
    }
}


