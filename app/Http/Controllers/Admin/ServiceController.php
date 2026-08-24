<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    // Liste avec filtre
    public function index(Request $request)
    {
        $query = Service::withCount('categories');

        // Filtre par nom ou par slug
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', '%' . $search . '%')
                  ->orWhere('slug', 'like', '%' . $search . '%');
            });
        }

        $services = $query->latest()->paginate(10)->withQueryString();

        return view('admin.services.index', compact('services'));
    }

    // Formulaire création
    public function create()
    {
        return view('admin.services.create');
    }

    // Enregistrer nouveau service
    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        Service::create($data);

        return redirect()->route('admin.services.index')
                         ->with('success', 'Service créé avec succès !');
    }

    // Formulaire édition
    public function edit(Service $service)
    {
        return view('admin.services.edit', compact('service'));
    }

    // Mise à jour
    public function update(Request $request, Service $service)
    {
        $data = $this->validated($request, $service);

        if ($request->hasFile('image')) {
            // Supprimer ancienne image si existante
            if ($service->image && Storage::disk('public')->exists($service->image)) {
                Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $service->update($data);

        return redirect()->route('admin.services.index')
                         ->with('success', 'Service mis à jour !');
    }

    // Supprimer
    public function destroy(Service $service)
    {
        if ($service->image && Storage::disk('public')->exists($service->image)) {
            Storage::disk('public')->delete($service->image);
        }

        $service->delete();

        return redirect()->route('admin.services.index')
                         ->with('success', 'Service supprimé !');
    }

    /**
     * Validation commune création / édition.
     *
     * Le slug est ce qui identifie le service côté front (/vente, /location...) :
     * il est normalisé et généré depuis le nom s'il n'est pas renseigné.
     */
    private function validated(Request $request, ?Service $service = null): array
    {
        $request->merge([
            'slug' => Str::slug($request->input('slug') ?: $request->input('nom')),
        ]);

        $data = $request->validate([
            'nom'         => 'required|string|max:255',
            'slug'        => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('services', 'slug')->ignore($service),
            ],
            'short_description' => 'nullable|string|max:120',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|max:2048',
        ], [
            'slug.regex'  => 'Le slug ne peut contenir que des lettres minuscules, des chiffres et des tirets.',
            'slug.unique' => 'Ce slug est déjà utilisé par un autre service.',
        ]);

        return collect($data)->only(['nom', 'slug', 'short_description', 'description'])->all();
    }
}
