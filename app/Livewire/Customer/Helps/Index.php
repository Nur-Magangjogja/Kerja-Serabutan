<?php

namespace App\Livewire\Customer\Helps;

use App\Models\Help;
use App\Models\City;
use App\Models\Rating;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
// (Schema/DB imports removed - restored original file state)
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithPagination, WithFileUploads;

    protected $queryString = [
        'statusFilter' => ['except' => 'menunggu_mitra'],
    ];

    public $statusFilter = 'menunggu_mitra';

    // Status-status yang termasuk dalam filter 'diproses'
    protected $diprosesStatuses = [
        'memperoleh_mitra',
        'taken',
        'partner_on_the_way',
        'partner_arrived',
        'in_progress',
        'sedang_diproses',
        'waiting_customer_confirmation',
        // show partner-cancel requests in "Diproses" tab for customer awareness
        'partner_cancel_requested',
    ];
    public $selectedHelpData = null;

    // Edit modal properties (declare as public so Livewire can bind to them)
    public $editingHelp = null;
    public $editTitle = null;
    public $editDescription = null;
    public $editAmount = null;
    public $editLocation = null;
    public $editFullAddress = null;
    public $editEquipmentProvided = null;
    public $editCityId = null;
    public $editLatitude = null;
    public $editLongitude = null;
    public $editPhoto = null;
    public $editExistingPhoto = null;
    public $editSearchResults = [];
    public $editCityQuery = null;
    public $cities = null;
    // Delete confirmation modal state
    public $showDeleteConfirm = false;
    public $deletingHelpId = null;

    /**
     * Trigger the delete confirmation modal for a given help id.
     */
    public function confirmDelete($id)
    {
        $help = Help::find($id);
        if (! $help || $help->user_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak memiliki izin untuk membatalkan bantuan ini.');
            return;
        }

        $this->deletingHelpId = $id;
        $this->showDeleteConfirm = true;
    }

    /**
     * Cancel the delete flow and hide the confirmation modal.
     */
    public function cancelDelete()
    {
        $this->deletingHelpId = null;
        $this->showDeleteConfirm = false;
    }

    /**
     * Perform the delete (or cancellation) action after confirmation.
     */
    public function deleteConfirmed()
    {
        if (! $this->deletingHelpId) {
            $this->showDeleteConfirm = false;
            return;
        }

        $help = Help::find($this->deletingHelpId);
        if (! $help || $help->user_id !== auth()->id()) {
            session()->flash('error', 'Permintaan tidak ditemukan atau Anda tidak memiliki izin.');
            $this->cancelDelete();
            return;
        }

        try {
            // Soft-delete or delete depending on model setup. Use delete() by default.
            $help->delete();
            session()->flash('message', 'Permintaan bantuan berhasil dibatalkan.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal membatalkan permintaan bantuan.');
        }

        $this->cancelDelete();
    }

    // --- Completion confirmation (from index list) ---
    public $confirmingHelpId = null;

    /**
     * Prepare the confirmation modal for marking a help as completed.
     */
    public function confirmCompletion($id)
    {
        $help = Help::find($id);
        if (! $help || $help->user_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak memiliki izin untuk mengonfirmasi pesanan ini.');
            return;
        }

        $this->confirmingHelpId = $id;
    }

    /**
     * Mark the help as completed after user confirmation.
     */
    public function completeConfirmed()
    {
        if (! $this->confirmingHelpId) {
            return;
        }

        $help = Help::find($this->confirmingHelpId);
        if (! $help || $help->user_id !== auth()->id()) {
            session()->flash('error', 'Permintaan tidak ditemukan atau Anda tidak memiliki izin.');
            $this->confirmingHelpId = null;
            return;
        }

        try {
            $help->update([
                'status' => 'selesai',
                'completed_at' => now(),
            ]);

            session()->flash('message', 'Permintaan telah ditandai selesai.');
        } catch (\Throwable $e) {
            session()->flash('error', 'Gagal menandai permintaan sebagai selesai.');
        }

        $this->confirmingHelpId = null;
    }
    // Rating flow
    public $ratingComment = null;
    public $pendingRating = null;
    public $pendingHelpForRating = null;
    

    // Temporary debug helper to confirm Livewire connectivity from browser
    public function testPing()
    {
        Log::info('Customer\Helps\Index::testPing invoked', ['user_id' => auth()->id()]);
        $this->selectedHelpData = [
            'id' => 0,
            'title' => 'Debug: Modal Test',
            'description' => 'This is a test modal opened from testPing().',
            'amount' => 0,
            'photo' => null,
            'location' => null,
            'user_name' => auth()->user()->name ?? null,
            'city_name' => null,
            'status' => 'test',
            'created_at_human' => now()->diffForHumans(),
        ];
    }

    // --- Edit flow ---
    public function editHelp($id)
    {
        $help = Help::findOrFail($id);

        // Only owner can edit
        if ($help->user_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak memiliki izin untuk mengedit bantuan ini.');
            return;
        }

        $this->editingHelp = $help->id;
        $this->editTitle = $help->title;
        $this->editDescription = $help->description;
        $this->editAmount = $help->amount;
        $this->editLocation = $help->location;
        $this->editFullAddress = $help->full_address;
        $this->editEquipmentProvided = $help->equipment_provided;
        $this->editCityId = $help->city_id;
        $this->editLatitude = $help->latitude;
        $this->editLongitude = $help->longitude;
        $this->editExistingPhoto = $help->photo;
        Log::info('Customer\\Helps\\Index::editHelp called', ['id' => $id, 'user_id' => auth()->id()]);

        // Dispatch a browser event with the help data so client-side listeners (and fallbacks) can react.
        $this->dispatch('open-edit', id: $help->id, title: $help->title, description: $help->description, amount: $help->amount, location: $help->location, city_id: $help->city_id, latitude: $help->latitude, longitude: $help->longitude);
    }

    // Set city id from search result (used by the edit modal search)
    public function setEditCityId($id)
    {
        $this->editCityId = $id;
        $city = City::find($id);
        if ($city) {
            $this->editCityQuery = $city->name . ' — ' . $city->province;
        }
        $this->editSearchResults = [];
    }

    public function updatedEditCityQuery($value)
    {
        $q = trim($value);
        if ($q === '') {
            $this->editSearchResults = [];
            return;
        }

        $limit = 10;

        $results = City::where('is_active', true)
            ->where(function ($builder) use ($q) {
            \Illuminate\Support\Facades\Log::info('updatedEditCityQuery called', ['q' => $q, 'user_id' => optional(auth()->user())->id]);
                $builder->where('name', 'like', "%{$q}%")
                        ->orWhere('province', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
            })
            ->whereRaw("COALESCE(code,'') NOT LIKE 'reqd-%' AND COALESCE(code,'') NOT LIKE 'regd-%'")
            ->select('id', 'name', 'province', 'code')
            ->orderBy('name')
            ->limit($limit)
            ->get()
            ->toArray();

        if (count($results) < $limit) {
            $remaining = $limit - count($results);
            $regRows = collect();

            if (Schema::hasTable('req_regencies') && Schema::hasTable('req_provinces')) {
                $regRows = \Illuminate\Support\Facades\DB::table('req_regencies')
                    ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
                    ->where(function ($builder) use ($q) {
                        $builder->where('req_regencies.regency', 'like', "%{$q}%")
                                ->orWhere('req_provinces.province', 'like', "%{$q}%");
                    })
                    ->select('req_regencies.id as regency_id', 'req_regencies.regency', 'req_regencies.type', 'req_provinces.province')
                    ->orderBy('req_regencies.regency')
                    ->limit($remaining)
                    ->get();

                \Illuminate\Support\Facades\Log::info('editCityQuery initial results', ['count' => count($results), 'remaining' => $remaining, 'q' => $q, 'regRows' => count($regRows)]);
            }

            if (count($regRows) < $remaining && Schema::hasTable('req_districts') && Schema::hasTable('req_regencies') && Schema::hasTable('req_provinces')) {
                $remaining2 = $remaining - count($regRows);
                $distRows = \Illuminate\Support\Facades\DB::table('req_districts')
                    ->join('req_regencies', 'req_districts.regency_id', '=', 'req_regencies.id')
                    ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
                    ->where(function ($builder) use ($q) {
                        $builder->where('req_districts.district', 'like', "%{$q}%")
                                ->orWhere('req_regencies.regency', 'like', "%{$q}%")
                                ->orWhere('req_provinces.province', 'like', "%{$q}%");
                    })
                    ->select(\Illuminate\Support\Facades\DB::raw("CONCAT('reqd-', req_districts.id) as regency_id"), 'req_districts.district as regency', 'req_regencies.regency as parent_regency', \Illuminate\Support\Facades\DB::raw('null as type'), 'req_provinces.province')
                    ->orderBy('req_districts.district')
                    ->limit($remaining2)
                    ->get();

                foreach ($distRows as $r) { $regRows->push($r); }
            }

            if (count($regRows) < $remaining && Schema::hasTable('reg_districts') && Schema::hasTable('reg_regencies') && Schema::hasTable('reg_provinces')) {
                $remaining3 = $remaining - count($regRows);
                $rows = \Illuminate\Support\Facades\DB::table('reg_districts')
                    ->join('reg_regencies', 'reg_districts.regency_id', '=', 'reg_regencies.id')
                    ->join('reg_provinces', 'reg_regencies.province_id', '=', 'reg_provinces.id')
                    ->where(function ($builder) use ($q) {
                        $builder->where('reg_districts.name', 'like', "%{$q}%")
                                ->orWhere('reg_regencies.name', 'like', "%{$q}%")
                                ->orWhere('reg_provinces.name', 'like', "%{$q}%");
                    })
                    ->select(\Illuminate\Support\Facades\DB::raw("CONCAT('regd-', reg_districts.id) as regency_id"), 'reg_districts.name as regency', 'reg_regencies.name as parent_regency', \Illuminate\Support\Facades\DB::raw('null as type'), 'reg_provinces.name as province')
                    ->orderBy('reg_districts.name')
                    ->limit($remaining3)
                    ->get();

                foreach ($rows as $r) { $regRows->push($r); }
            }

            if (count($regRows) < $remaining && Schema::hasTable('reg_regencies') && Schema::hasTable('reg_provinces')) {
                $remaining2 = $remaining - count($regRows);
                $rows = \Illuminate\Support\Facades\DB::table('reg_regencies')
                    ->join('reg_provinces', 'reg_regencies.province_id', '=', 'reg_provinces.id')
                    ->where(function ($builder) use ($q) {
                        $builder->where('reg_regencies.name', 'like', "%{$q}%")
                                ->orWhere('reg_provinces.name', 'like', "%{$q}%");
                    })
                    ->select('reg_regencies.id as regency_id', 'reg_regencies.name as regency', \Illuminate\Support\Facades\DB::raw('null as type'), 'reg_provinces.name as province')
                    ->orderBy('reg_regencies.name')
                    ->limit($remaining2)
                    ->get();

                foreach ($rows as $r) { $regRows->push($r); }
            }

            if (count($regRows) < $remaining && Schema::hasTable('regencies') && Schema::hasTable('provinces')) {
                $remaining3 = $remaining - count($regRows);
                $rows = \Illuminate\Support\Facades\DB::table('regencies')
                    ->join('provinces', 'regencies.province_id', '=', 'provinces.id')
                    ->where(function ($builder) use ($q) {
                        $builder->where('regencies.regency', 'like', "%{$q}%")
                                ->orWhere('provinces.province', 'like', "%{$q}%");
                    })
                    ->select('regencies.id as regency_id', 'regencies.regency', 'regencies.type', 'provinces.province')
                    ->orderBy('regencies.regency')
                    ->limit($remaining3)
                    ->get();

                foreach ($rows as $r) { $regRows->push($r); }
            }

            foreach ($regRows as $r) {
                $regencyIdStr = is_string($r->regency_id) ? $r->regency_id : (string) $r->regency_id;
                $display = null;
                $targetCity = null;

                if (strpos($regencyIdStr, 'reqd-') === 0) {
                    try {
                        $did = substr($regencyIdStr, 5);
                        $parentRow = \Illuminate\Support\Facades\DB::table('req_districts')
                            ->join('req_regencies','req_districts.regency_id','=','req_regencies.id')
                            ->join('req_provinces','req_regencies.province_id','=','req_provinces.id')
                            ->where('req_districts.id', $did)
                            ->select('req_districts.district as district','req_regencies.id as parent_regency_id','req_regencies.regency as parent_regency','req_provinces.province')
                            ->first();
                        if ($parentRow) {
                            $parentCode = 'reqr-' . $parentRow->parent_regency_id;
                            $targetCity = City::firstOrCreate(
                                ['code' => $parentCode],
                                ['name' => $parentRow->parent_regency, 'province' => $parentRow->province, 'is_active' => true]
                            );
                            $display = $parentRow->district . ', ' . $parentRow->parent_regency . ', ' . $parentRow->province;
                        }
                    } catch (\Throwable $e) {}
                } elseif (strpos($regencyIdStr, 'regd-') === 0) {
                    try {
                        $did = substr($regencyIdStr, 5);
                        $parentRow = \Illuminate\Support\Facades\DB::table('reg_districts')
                            ->join('reg_regencies','reg_districts.regency_id','=','reg_regencies.id')
                            ->join('reg_provinces','reg_regencies.province_id','=','reg_provinces.id')
                            ->where('reg_districts.id', $did)
                            ->select('reg_districts.name as district','reg_regencies.id as parent_regency_id','reg_regencies.name as parent_regency','reg_provinces.name as province')
                            ->first();
                        if ($parentRow) {
                            $parentCode = 'reqr-' . $parentRow->parent_regency_id;
                            $targetCity = City::firstOrCreate(
                                ['code' => $parentCode],
                                ['name' => $parentRow->parent_regency, 'province' => $parentRow->province, 'is_active' => true]
                            );
                            $display = $parentRow->district . ', ' . $parentRow->parent_regency . ', ' . $parentRow->province;
                        }
                    } catch (\Throwable $e) {}
                }

                if (! $targetCity) {
                    $targetCity = City::firstOrCreate(
                        ['code' => $r->regency_id],
                        ['name' => $r->regency, 'province' => $r->province, 'type' => $r->type ?? null, 'is_active' => true]
                    );
                    if (! $display && ! empty($r->parent_regency)) {
                        $display = $r->regency . ', ' . $r->parent_regency . ', ' . $r->province;
                    }
                }

                if (! $display) {
                    $display = $targetCity->name . ', ' . $targetCity->province;
                }

                $exists = false;
                foreach ($results as $res) { if ($res['id'] == $targetCity->id) { $exists = true; break; } }
                if (! $exists) {
                    $item = ['id' => $targetCity->id, 'name' => $targetCity->name, 'province' => $targetCity->province, 'code' => $targetCity->code];
                    if ($display) $item['display'] = $display;
                    $results[] = $item;
                }
            }
        }

        // try to enrich district-coded results with full display
        foreach ($results as $idx => $res) {
            if (! empty($res['code']) && is_string($res['code'])) {
                $code = $res['code'];
                try {
                    if (strpos($code, 'reqd-') === 0 && Schema::hasTable('req_districts')) {
                        $did = substr($code, 5);
                        $row = DB::table('req_districts')
                            ->join('req_regencies', 'req_districts.regency_id', '=', 'req_regencies.id')
                            ->join('req_provinces', 'req_regencies.province_id', '=', 'req_provinces.id')
                            ->where('req_districts.id', $did)
                            ->select('req_districts.district as district', 'req_regencies.regency as regency', 'req_provinces.province as province')
                            ->first();
                        if ($row) {
                            $results[$idx]['display'] = $row->district . ', ' . $row->regency . ', ' . $row->province;
                        }
                    } elseif (strpos($code, 'regd-') === 0 && Schema::hasTable('reg_districts')) {
                        $did = substr($code, 5);
                        $row = DB::table('reg_districts')
                            ->join('reg_regencies', 'reg_districts.regency_id', '=', 'reg_regencies.id')
                            ->join('reg_provinces', 'reg_regencies.province_id', '=', 'reg_provinces.id')
                            ->where('reg_districts.id', $did)
                            ->select('reg_districts.name as district', 'reg_regencies.name as regency', 'reg_provinces.name as province')
                            ->first();
                        if ($row) {
                            $results[$idx]['display'] = $row->district . ', ' . $row->regency . ', ' . $row->province;
                        }
                    }
                } catch (\Throwable $e) {}
            }
        }

        $this->editSearchResults = $results;
    }

    /**
     * Called when a customer clicks a rating star.
     * Stores the pending rating value and which help is being rated.
     */
    public function setRating($helpId, $value)
    {
        $this->pendingHelpForRating = $helpId;
        $this->pendingRating = (int) $value;
    }

    /**
            \Illuminate\Support\Facades\Log::info('updatedEditCityQuery finished', ['q' => $q, 'results_count' => count($results)]);
     * Submit rating and optional comment for a help.
     */
    public function submitRating($helpId)
    {
        $this->validate([
            'pendingRating' => 'required|integer|min:1|max:5',
            'ratingComment' => 'nullable|string|max:1000',
        ]);

        $help = Help::findOrFail($helpId);

        // Only the owner (customer) may submit rating for this help
        if ($help->user_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak memiliki izin untuk memberi rating pada bantuan ini.');
            return;
        }

        Rating::updateOrCreate(
            ['help_id' => $helpId, 'user_id' => auth()->id()],
            [
                'mitra_id' => $help->mitra_id,
                'rating' => $this->pendingRating,
                'review' => $this->ratingComment,
            ]
        );

        session()->flash('message', 'Terima kasih — rating Anda telah disimpan.');

        // reset rating state
        $this->pendingHelpForRating = null;
        $this->pendingRating = null;
        $this->ratingComment = null;
    }

    public function saveEdit()
    {
        $this->validate([
            'editTitle' => 'required|string|max:255',
            'editDescription' => 'required|string',
            'editAmount' => 'required|numeric|min:10000|max:100000000',
            'editLocation' => 'nullable|string|max:255',
            'editFullAddress' => 'nullable|string|max:500',
            'editEquipmentProvided' => 'nullable|string|max:1000',
            'editCityId' => 'required|exists:cities,id',
            'editPhoto' => 'nullable|image|max:2048', // 2MB max
        ]);

        $help = Help::findOrFail($this->editingHelp);

        if ($help->user_id !== auth()->id()) {
            session()->flash('error', 'Anda tidak memiliki izin untuk mengedit bantuan ini.');
            return;
        }

        $updateData = [
            'title' => $this->editTitle,
            'description' => $this->editDescription,
            'amount' => $this->editAmount,
            'location' => $this->editLocation,
            'full_address' => $this->editFullAddress,
            'equipment_provided' => $this->editEquipmentProvided,
            'city_id' => $this->editCityId,
            'latitude' => $this->editLatitude,
            'longitude' => $this->editLongitude,
        ];

        // Handle photo upload
        if ($this->editPhoto) {
            // Delete old photo if exists
            if ($help->photo) {
                Storage::disk('public')->delete($help->photo);
            }
            $updateData['photo'] = $this->editPhoto->store('helps', 'public');
        }

        $help->update($updateData);

        session()->flash('message', 'Perubahan bantuan berhasil disimpan.');
        $this->closeEdit();
    }

    public function closeEdit()
    {
        $this->editingHelp = null;
        $this->editTitle = null;
        $this->editDescription = null;
        $this->editAmount = null;
        $this->editLocation = null;
        $this->editFullAddress = null;
        $this->editEquipmentProvided = null;
        $this->editCityId = null;
        $this->editLatitude = null;
        $this->editLongitude = null;
        $this->editPhoto = null;
        $this->editExistingPhoto = null;
    }

    public function render()
    {
        $user = auth()->user();

        if ($user->isCustomer()) {
            $helps = Help::where('user_id', $user->id)
                ->with([
                    'city',
                    'mitra',
                    'rating' => function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    },
                ])
                ->withCount('chatMessages')
                ->when($this->statusFilter !== '', function ($query) {
                    if ($this->statusFilter === 'diproses') {
                        $query->whereIn('status', $this->diprosesStatuses);
                    } else {
                        $query->where('status', $this->statusFilter);
                    }
                })
                ->latest()
                ->paginate(10);
        } elseif ($user->isMitra()) {
                if (request()->routeIs('helps.available')) {
                // Mitra melihat bantuan yang available
                $helps = Help::where('status', 'approved')
                    ->whereNull('mitra_id')
                    ->with(['user', 'city'])
                        ->withCount('chatMessages')
                    ->latest()
                    ->paginate(10);
            } else {
                // Mitra melihat bantuan yang sudah diambil
                $helps = Help::where('mitra_id', $user->id)
                    ->with(['user', 'city'])
                        ->withCount('chatMessages')
                    ->when($this->statusFilter !== '', function ($query) {
                        $query->where('status', $this->statusFilter);
                    })
                    ->latest()
                    ->paginate(10);
            }
        } else {
            $helps = collect();
        }

        // Safe debug: render the view with the component's essential public
        // properties passed in so we can capture the actual HTML Livewire sees
        // (helps, statusFilter, editingHelp). This helps locate stray top-level
        // elements that trigger Livewire's multiple-root detection.
        try {
            $debugHtml = view('livewire.customer.helps.index', [
                'helps' => $helps,
                'statusFilter' => $this->statusFilter,
                'editingHelp' => $this->editingHelp,
            ])->render();

            // Use DOMDocument to count top-level element children inside the
            // rendered fragment. If count > 1, Livewire will complain about
            // multiple root elements.
            libxml_use_internal_errors(true);
            $dom = new \DOMDocument();
            $dom->loadHTML('<!doctype html><html><body>' . $debugHtml . '</body></html>');
            $body = $dom->getElementsByTagName('body')->item(0);

            $rootTags = [];
            $rootCount = 0;
            if ($body) {
                foreach ($body->childNodes as $child) {
                    if ($child->nodeType === XML_ELEMENT_NODE) {
                        $rootCount++;
                        $rootTags[] = $child->nodeName;
                    }
                }
            }

            \Illuminate\Support\Facades\Log::info('Debug rendered customer.helps roots', ['count' => $rootCount, 'tags' => $rootTags]);
            \Illuminate\Support\Facades\Log::info('Debug rendered customer.helps HTML (first 3000 chars)', ['html_snippet' => substr($debugHtml, 0, 3000)]);
            libxml_clear_errors();
            // Additionally, write the full rendered HTML to storage for inspection when
            // running locally. This helps examining the exact markup Livewire received.
            try {
                if (app()->environment('local') || app()->environment('development') || app()->runningUnitTests()) {
                    $path = storage_path('debug_customer_helps.html');
                    file_put_contents($path, $debugHtml);
                    \Illuminate\Support\Facades\Log::info('Wrote debug_customer_helps.html for inspection', ['path' => $path]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Failed writing debug HTML file', ['exception' => $e->getMessage()]);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Debug render error for customer.helps', ['exception' => $e->getMessage()]);
        }

        // load active cities for the edit form
        $this->cities = City::where('is_active', true)->orderBy('name')->get();

        return view('livewire.customer.helps.index', [
            'helps' => $helps,
            'cities' => $this->cities,
        ]);
    }
}
