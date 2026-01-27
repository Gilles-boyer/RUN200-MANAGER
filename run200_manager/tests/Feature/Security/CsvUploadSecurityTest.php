<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\Race;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CsvUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Race $race;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('ADMIN', 'web');
        Permission::findOrCreate('race.manage', 'web');
        Role::findByName('ADMIN')->givePermissionTo('race.manage');

        $this->admin = User::factory()->create();
        $this->admin->assignRole('ADMIN');

        $this->race = Race::factory()->create(['status' => 'CLOSED']);

        Storage::fake('local');
    }

    public function test_rejects_files_with_invalid_extension(): void
    {
        $file = UploadedFile::fake()->create('malicious.php', 100);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Staff\Results\ResultsManager::class, ['race' => $this->race])
            ->set('csvFile', $file)
            ->call('uploadCsv')
            ->assertHasErrors(['csvFile']);
    }

    public function test_rejects_files_with_double_extension(): void
    {
        $file = UploadedFile::fake()->create('data.csv.php', 100);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Staff\Results\ResultsManager::class, ['race' => $this->race])
            ->set('csvFile', $file)
            ->call('uploadCsv')
            ->assertHasErrors(['csvFile']);
    }

    public function test_rejects_oversized_files(): void
    {
        $file = UploadedFile::fake()->create('large.csv', 6000);

        Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Staff\Results\ResultsManager::class, ['race' => $this->race])
            ->set('csvFile', $file)
            ->call('uploadCsv')
            ->assertHasErrors(['csvFile']);
    }

    public function test_accepts_valid_csv_file(): void
    {
        $csvContent = "position,bib,pilote,voiture,categorie,temps\n1,42,Jean Dupont,Porsche 911,GT,01:23:45.678";
        $file = UploadedFile::fake()->createWithContent('results.csv', $csvContent);

        $component = Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Staff\Results\ResultsManager::class, ['race' => $this->race])
            ->set('csvFile', $file)
            ->call('uploadCsv');

        $component->assertHasNoErrors(['csvFile']);
    }

    public function test_accepts_valid_txt_file(): void
    {
        $csvContent = "position;bib;pilote;voiture;categorie;temps\n1;42;Jean Dupont;Porsche 911;GT;01:23:45.678";
        $file = UploadedFile::fake()->createWithContent('results.txt', $csvContent);

        $component = Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Staff\Results\ResultsManager::class, ['race' => $this->race])
            ->set('csvFile', $file)
            ->call('uploadCsv');

        $component->assertHasNoErrors(['csvFile']);
    }

    public function test_rejects_file_with_too_many_lines(): void
    {
        $header = "position,bib,pilote,voiture,categorie,temps\n";
        $lines = [];
        for ($i = 1; $i <= 10001; $i++) {
            $lines[] = $i.','.$i.',Pilote '.$i.',Voiture,GT,01:00:00.000';
        }
        $csvContent = $header.implode("\n", $lines);

        $file = UploadedFile::fake()->createWithContent('too_many_rows.csv', $csvContent);

        $component = Livewire::actingAs($this->admin)
            ->test(\App\Livewire\Staff\Results\ResultsManager::class, ['race' => $this->race])
            ->set('csvFile', $file)
            ->call('uploadCsv');

        $component->assertSet('errorMessage', 'Le fichier CSV ne peut pas contenir plus de 10 000 lignes.');
    }
}
