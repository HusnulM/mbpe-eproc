<?php

namespace Tests\Feature;

use App\Imports\StockOpnamImport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use App\Models\User;

class StockOpnamImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // login user
        $this->user = User::factory()->create([
            'name' => 'tester',
            'email' => 'tester@example.com',
        ]);

        Auth::login($this->user);

        Storage::fake('public');
    }

    /** @test */
    public function it_inserts_header_detail_and_files()
    {
        $req = [
            'tglupload' => now()->format('Y-m-d'),
            'remark' => 'Test stock opname',
            'whscode' => 'WH01',
            'efile' => [ UploadedFile::fake()->create('dokumen.pdf', 100) ]
        ];

        $rows = collect([
            [
                'part_number' => 'MAT001',
                'part_name' => 'Material Satu',
                'actual_stock' => 10,
                'uom' => 'PCS',
                'harga_satuan' => 5000
            ]
        ]);

        $import = new StockOpnamImport($req);
        $result = $import->collection($rows);

        $this->assertTrue($result);

        $this->assertDatabaseHas('t_stock_opnam01', [
            'pidnote' => 'Test stock opname',
            'whsid' => 'WH01',
            'piduser' => 'tester',
        ]);

        $this->assertDatabaseHas('t_stock_opnam02', [
            'material' => 'MAT001',
            'total_price' => 50000,
        ]);

        $this->assertDatabaseHas('t_attachments', [
            'efile' => 'dokumen.pdf',
        ]);
    }

    /** @test */
    public function it_rolls_back_if_no_rows()
    {
        $req = [
            'tglupload' => now()->format('Y-m-d'),
            'remark' => 'Empty test',
            'whscode' => 'WH01',
        ];

        $rows = collect([]);

        $import = new StockOpnamImport($req);
        $result = $import->collection($rows);

        $this->assertFalse($result);

        $this->assertDatabaseMissing('t_stock_opnam01', [
            'pidnote' => 'Empty test',
        ]);
    }

    /** @test */
    public function it_inserts_approval_flow_if_available()
    {
        // Seed fake workflow_budget (simulasi view)
        DB::table('v_workflow_budget')->insert([
            'object' => 'OPNAM',
            'requester' => $this->user->id,
            'approver' => 999,
            'approver_level' => 1,
        ]);

        $req = [
            'tglupload' => now()->format('Y-m-d'),
            'remark' => 'With approval',
            'whscode' => 'WH02',
        ];

        $rows = collect([
            [
                'part_number' => 'MAT100',
                'part_name' => 'Material Approval',
                'actual_stock' => 5,
                'uom' => 'PCS',
                'harga_satuan' => 1000
            ]
        ]);

        $import = new StockOpnamImport($req);
        $result = $import->collection($rows);

        $this->assertTrue($result);

        // harus ada record approval
        $this->assertDatabaseHas('t_opnam_approval', [
            'approver' => 999,
            'approver_level' => 1,
            'is_active' => 'Y',
        ]);
    }

    /** @test */
    public function it_auto_approves_if_no_workflow_budget()
    {
        $req = [
            'tglupload' => now()->format('Y-m-d'),
            'remark' => 'No approval test',
            'whscode' => 'WH03',
        ];

        $rows = collect([
            [
                'part_number' => 'MAT200',
                'part_name' => 'Material Auto Approve',
                'actual_stock' => 7,
                'uom' => 'PCS',
                'harga_satuan' => 2000
            ]
        ]);

        $import = new StockOpnamImport($req);
        $result = $import->collection($rows);

        $this->assertTrue($result);

        // ambil header
        $header = DB::table('t_stock_opnam01')->where('pidnote', 'No approval test')->first();

        $this->assertEquals('A', $header->approval_status);
    }
}
