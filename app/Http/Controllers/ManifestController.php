<?php

namespace App\Http\Controllers;

use App\Models\CostEstimate;
use App\Models\Manifest;
use App\Models\ManifestEquipment;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ManifestDriver;

use Illuminate\Support\Facades\DB;
use App\Models\Stop;
use App\Models\Task;
use Illuminate\Support\Facades\Storage;

class ManifestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Manifest::with(['company', 'stops', 'costEstimates', 'manifestEquipment.equipment', 'manifestDrivers.driver']);
        
        // If user is not super admin, filter by their company
        if (!$user->is_super_admin) {
            $query->where('company_id', $user->company_id);
        }
        
        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('manifestDrivers.driver', function($driverQuery) use ($search) {
                      $driverQuery->where('f_name', 'like', "%{$search}%")
                                  ->orWhere('l_name', 'like', "%{$search}%")
                                  ->orWhere(DB::raw("CONCAT(f_name, ' ', l_name)"), 'like', "%{$search}%");
                  })
                  ->orWhereHas('manifestEquipment.equipment', function($equipmentQuery) use ($search) {
                      $equipmentQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('date_from')) {
            $query->where('start_date', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('start_date', '<=', $request->date_to);
        }
        
        // Get page size from request or default to 15
        $perPage = $request->get('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 25, 50, 100]) ? $perPage : 15;
        
        $manifests = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        // Append query parameters to pagination links
        $manifests->appends($request->query());
        
        return view('dashboard.manifest.view', compact('manifests'));
    }
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'start_date' => 'nullable|string|max:255',
            'previous_stop' => 'nullable|string|max:255',
            'next_stop' => 'nullable|string|max:255',
            'freight' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:255',
            'task_id' => 'nullable|exists:tasks,id',
            'driver_id' => 'nullable|exists:drivers,id',
        ]);

        try {
            // Generate a sequential code (e.g., INV3023, INV3024)
            $latestManifest = Manifest::latest('id')->first(); // Get the latest manifest based on the highest ID

            // Default to 1 if no manifest exists
            $nextCodeNumber = $latestManifest
                ? (int) substr($latestManifest->code, 3) + 1
                : 3023;

            // Create the new manifest code
            $newCode = 'INV' . $nextCodeNumber;

            // Prepare the data for creating a new manifest
            $data = $request->all();
            $data['code'] = $newCode; // Assign the newly generated code

            // Create a new Manifest record
            $manifest = Manifest::create($data);

            // Redirect to the manifest view with a success message
            return redirect()
                ->route('manifest.view')
                ->with('success', 'Manifest created successfully!');
        } catch (\Exception $e) {
            // Catch any exceptions and return an error message
            return redirect()
                ->back()
                ->with('error', 'Error occurred: ' . $e->getMessage());
        }
    }

    public function quickCreate(Request $request)
    {
        try {
            $companyId = Auth::user()->company_id;

            // Generate a sequential code
            $latestManifest = Manifest::where('company_id', $companyId)->latest('id')->first();
            $nextCodeNumber = $latestManifest && preg_match('/INV(\d+)/', $latestManifest->code, $matches)
                ? (int) $matches[1] + 1
                : 3023;
            $newCode = 'INV' . $nextCodeNumber;

            // Create the Manifest
            $manifest = Manifest::create([
                'company_id' => $companyId,
                'code' => $newCode,
                'start_date' => $request->input('start_date', now()),
                'status' => $request->input('status', 'active'),
            ]);

            // Assign to Order if requested
            if ($request->filled('assign_order_id')) {
                $order = \App\Models\Order::where('id', $request->assign_order_id)
                    ->where('company_id', $companyId)
                    ->first();
                
                if ($order) {
                    $order->update(['manifest_id' => $manifest->id]);
                }
            }

            return response()->json([
                'success' => true,
                'manifest' => $manifest,
                'message' => 'Manifest created successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        $code = rand(10, 9999);

        $manifest = Manifest::where('draft', 1)
            ->where('company_id', Auth::user()->company_id)
            ->first();
        if (!$manifest) {
            $manifest = Manifest::create([
                'company_id' => Auth::user()->company_id,
                'code' => "INV$code",
                'status' => 'draft',
                'draft' => 1,
            ]);
        }
        // Retrieve only stops and cost estimates belonging to the created manifest
        $stops = Stop::where('manifest_id', $manifest->id)->get();
        $costEstimates = CostEstimate::where(
            'manifest_id',
            $manifest->id
        )->get();

        return redirect()->route('manifest.edit', $manifest->id);
    }

   

    public function ViewEdit($id)
    {
        $manifest = Manifest::find($id);
       
        // Changed code: Pass manifestId to view along with stops and cost estimates
        $manifestId = $manifest->id;
        $stops = Stop::where('manifest_id', $manifest->id)->get();
        $costEstimates = CostEstimate::where(
            'manifest_id',
            $manifest->id
        )->get();

        // Get assigned equipment with details
        $assigned_equipments = ManifestEquipment::where('manifest_id', $id)
            ->with('equipment')
            ->get();
            
        // Get assigned drivers with details
        $assigned_drivers = ManifestDriver::where('manifest_id', $id)
            ->with('driver')
            ->get();

        $tasks = Task::where('manifest_id', $id)->get();
           
        return view(
            'dashboard.manifest.edit.edit',
            compact('manifest', 'manifestId', 'stops', 'costEstimates', 'assigned_equipments', 'assigned_drivers', 'tasks','manifest')
        );
    }

    public function destroy($id)
    {
        $manifest = Manifest::find($id);
        if (!$manifest) {
            return redirect()
                ->route('manifest.index')
                ->with('error', 'Manifest not found.');
        }
        $manifest->delete();
        return redirect()
            ->route('manifest.index')
            ->with('success', 'Manifest deleted successfully!');
    }

    public function uploadDocument(Request $request)
    {
        $request->validate([
            'manifest_document' => 'required|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif|max:10240'
        ]);
    
        if ($request->hasFile('manifest_document')) {
            $file = $request->file('manifest_document');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('manifest-documents', $filename, 'public');
    
            // Update the database with the file path
            $manifestId = $request->input('record_id');
            Manifest::where('id', $manifestId)->update([
                'manifest_document' => $path
            ]);
    
            return response()->json([
                'success' => true,
                'file' => $path,
                'message' => 'Document uploaded successfully'
            ]);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'Upload failed'
        ]);
    }

    public function deleteDocument($id)
    {
        $manifest = Manifest::findOrFail($id);
        
        if ($manifest->manifest_document) {
            Storage::delete($manifest->manifest_document);
            $manifest->manifest_document = null;
            $manifest->save();
            
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false, 'message' => 'No document found']);
    }
}
