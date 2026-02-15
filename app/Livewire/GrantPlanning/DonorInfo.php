<?php

namespace App\Livewire\GrantPlanning;

use App\Models\Autocomplete;
use App\Models\Donor;
use App\Models\Grant;
use App\Repositories\GrantPlanningRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class DonorInfo extends Component
{
    public Grant $grant;

    public ?int $selectedDonorId = null;

    public string $name = '';

    public string $origin = '';

    public string $address = '';

    public string $country = '';

    public string $category = '';

    public string $phone = '';

    public string $email = '';

    public string $provinceId = '';

    public string $regencyId = '';

    public string $districtId = '';

    public string $villageId = '';

    public function mount(Grant $grant): void
    {
        $userUnit = auth()->user()->unit;
        abort_unless($grant->id_satuan_kerja === $userUnit->id_user, 403);
        abort_unless(app(GrantPlanningRepository::class)->isEditable($grant), 403);

        $this->grant = $grant;

        if ($grant->id_pemberi_hibah) {
            $this->selectedDonorId = $grant->id_pemberi_hibah;
            $this->fillFromDonor($grant->id_pemberi_hibah);
        }
    }

    public function updatedName(): void
    {
        $this->selectedDonorId = null;
    }

    public function selectDonor(int $donorId): void
    {
        $this->selectedDonorId = $donorId;
        $this->fillFromDonor($donorId);
    }

    public function updatedOrigin(): void
    {
        if ($this->origin === 'DALAM NEGERI') {
            $this->country = 'INDONESIA';
        } else {
            $this->country = '';
        }

        $this->category = '';
        $this->provinceId = '';
        $this->regencyId = '';
        $this->districtId = '';
        $this->villageId = '';
    }

    public function updatedProvinceId(): void
    {
        $this->regencyId = '';
        $this->districtId = '';
        $this->villageId = '';
    }

    public function updatedRegencyId(): void
    {
        $this->districtId = '';
        $this->villageId = '';
    }

    public function updatedDistrictId(): void
    {
        $this->villageId = '';
    }

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'origin' => ['required', 'string', 'in:DALAM NEGERI,LUAR NEGERI'],
            'address' => ['required', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
        ];

        if ($this->origin === 'LUAR NEGERI') {
            $rules['country'] = ['required', 'string', 'max:255'];
        }

        if ($this->origin === 'DALAM NEGERI') {
            $rules['provinceId'] = ['required', 'string'];
            $rules['regencyId'] = ['required', 'string'];
            $rules['districtId'] = ['required', 'string'];
            $rules['villageId'] = ['required', 'string'];
        }

        return $rules;
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => __('page.grant-planning-donor.label-name'),
            'origin' => __('page.grant-planning-donor.label-origin'),
            'address' => __('page.grant-planning-donor.label-address'),
            'category' => __('page.grant-planning-donor.label-category'),
            'phone' => __('page.grant-planning-donor.label-phone'),
            'email' => __('page.grant-planning-donor.label-email'),
            'country' => __('page.grant-planning-donor.label-country'),
            'provinceId' => __('page.grant-planning-donor.label-province'),
            'regencyId' => __('page.grant-planning-donor.label-regency'),
            'districtId' => __('page.grant-planning-donor.label-district'),
            'villageId' => __('page.grant-planning-donor.label-village'),
        ];
    }

    public function save(GrantPlanningRepository $repository): void
    {
        if (! $this->selectedDonorId) {
            $this->validate();
        }

        if ($this->selectedDonorId) {
            $donorId = $this->selectedDonorId;
        } else {
            $data = [
                'nama' => str($this->name)->upper()->toString(),
                'asal' => $this->origin,
                'alamat' => str($this->address)->upper()->toString(),
                'kategori' => $this->category ?: null,
                'nomor_telepon' => $this->phone,
                'email' => $this->email ? str($this->email)->lower()->toString() : null,
            ];

            if ($this->origin === 'DALAM NEGERI') {
                $data['negara'] = 'INDONESIA';
                $data['kode_provinsi'] = $this->provinceId;
                $data['nama_provinsi'] = $this->resolveRegionName($this->provinceId, 'provinsi.json');
                $data['kode_kabupaten_kota'] = $this->regencyId;
                $data['nama_kabupaten_kota'] = $this->resolveRegionName($this->regencyId, "kabupaten-kota/{$this->provinceId}.json");
                $data['kode_kecamatan'] = $this->districtId;
                $data['nama_kecamatan'] = $this->resolveRegionName($this->districtId, "kecamatan/{$this->regencyId}.json");
                $data['kode_desa_kelurahan'] = $this->villageId;
                $data['nama_desa_kelurahan'] = $this->resolveRegionName($this->villageId, "desa-kelurahan/{$this->districtId}.json");
            } else {
                $data['negara'] = $this->country;
            }

            $donor = $repository->createDonor($data);
            $donorId = $donor->id;
        }

        $repository->linkDonor($this->grant, $donorId);

        $this->redirect(route('grant-planning.proposal-document', $this->grant), navigate: true);
    }

    public function render(GrantPlanningRepository $repository)
    {
        $provinceOptions = [];
        $regencyOptions = [];
        $districtOptions = [];
        $villageOptions = [];

        if ($this->origin === 'DALAM NEGERI') {
            $provinceOptions = $this->fetchRegions('provinsi.json');

            if ($this->provinceId) {
                $regencyOptions = $this->fetchRegions("kabupaten-kota/{$this->provinceId}.json");
            }

            if ($this->regencyId) {
                $districtOptions = $this->fetchRegions("kecamatan/{$this->regencyId}.json");
            }

            if ($this->districtId) {
                $villageOptions = $this->fetchRegions("desa-kelurahan/{$this->districtId}.json");
            }
        }

        $categoryOptions = [];
        if ($this->origin) {
            $identifier = $this->origin === 'DALAM NEGERI'
                ? 'kategori_pemberi_hibah.dalam_negeri'
                : 'kategori_pemberi_hibah.luar_negeri';

            $categoryOptions = Autocomplete::where('identifier', $identifier)
                ->orderBy('value')
                ->pluck('value')
                ->all();
        }

        $matchingDonors = collect();
        if (mb_strlen($this->name) >= 2 && ! $this->selectedDonorId) {
            $matchingDonors = Donor::query()
                ->where('nama', 'ilike', "%{$this->name}%")
                ->limit(10)
                ->get();
        }

        return view('livewire.grant-planning.donor-info', [
            'provinceOptions' => $provinceOptions,
            'regencyOptions' => $regencyOptions,
            'districtOptions' => $districtOptions,
            'villageOptions' => $villageOptions,
            'categoryOptions' => $categoryOptions,
            'matchingDonors' => $matchingDonors,
        ]);
    }

    private function fillFromDonor(int $donorId): void
    {
        $donor = Donor::find($donorId);

        if (! $donor) {
            return;
        }

        $this->name = $donor->nama;
        $this->origin = $donor->asal ?? '';
        $this->address = $donor->alamat ?? '';
        $this->country = $donor->negara ?? '';
        $this->category = $donor->kategori ?? '';
        $this->phone = $donor->nomor_telepon ?? '';
        $this->email = $donor->email ?? '';

        $this->provinceId = $donor->kode_provinsi ?? '';
        $this->regencyId = $donor->kode_kabupaten_kota ?? '';
        $this->districtId = $donor->kode_kecamatan ?? '';
        $this->villageId = $donor->kode_desa_kelurahan ?? '';
    }

    private function resolveRegionName(string $id, string $path): ?string
    {
        $options = $this->fetchRegions($path);
        $region = collect($options)->firstWhere('id', $id);

        return $region['name'] ?? null;
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    private function fetchRegions(string $path): array
    {
        return Cache::remember("regions:{$path}", now()->addDay(), function () use ($path) {
            $response = Http::get("https://lamaccatech.github.io/wilayah-indonesia/{$path}");

            return $response->successful() ? $response->json() : [];
        });
    }
}
