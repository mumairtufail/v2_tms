<?php

namespace App\Http\Controllers;

use App\Models\Stop;
use App\Models\Manifest;
use Illuminate\Http\Request;

class StopController extends Controller
{
    public function createStop(Request $request)
    {
        $request->validate([
            'manifest_id' => 'required|exists:manifests,id',
            'location'    => 'nullable|string',
            'company'     => 'nullable|string',
            'address1'    => 'nullable|string',
            'address2'    => 'nullable|string',
            'city'        => 'nullable|string',
            'state'       => 'nullable|string',
            'country'     => 'nullable|string',
            'postal'      => 'nullable|string',
        ]);

        $stop = Stop::create($request->all());

        $manifest = Manifest::find($request->manifest_id);
        if ($manifest && $manifest->draft == 1) {
            $manifest->draft = 0;
            $manifest->save();
        }

        return redirect()->route('manifest.edit', $manifest->id)
                         ->with('success', 'Stop added successfully.');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'stop' => $stop
            ]);
        }

        return redirect()->back()->with('success', 'Stop added successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'company'     => 'nullable|string',
            'address1'    => 'nullable|string',
            'address2'    => 'nullable|string',
            'city'        => 'nullable|string',
            'state'       => 'nullable|string',
            'country'     => 'nullable|string',
            'postal'      => 'nullable|string',
        ]);

        $stop = Stop::findOrFail($id);
        $stop->update($request->all());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'stop' => $stop
            ]);
        }

        return redirect()->back()->with('success', 'Stop updated successfully.');
    }

    public function destroy($id)
    {
        $stop = Stop::findOrFail($id);
        $stop->delete();

        return response()->json([
            'success' => true,
            'message' => 'Stop deleted successfully'
        ]);
    }
}
