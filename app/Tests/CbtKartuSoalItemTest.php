<?php

namespace App\Tests;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Models\CbtKartuSoalItemModel;

/**
 * Tests untuk query kartu soal hasil refactor tahap 2:
 * - getItemsWithTujuan() dengan filter SQL + LIMIT/OFFSET
 * - countItemsWithTujuan() sebagai pasangan pagination (COUNT DISTINCT)
 * - nextNomor(), getKartuSummary(), getTotalEstimasi(), getLinkedTujuanUniqueCount()
 *
 * PERINGATAN: membutuhkan database TEST terpisah (DatabaseTestTrait + migrate).
 * JANGAN dijalankan dengan config database produksi.
 *   database.tests.hostname = ...
 *   database.tests.database = ..._test
 */
class CbtKartuSoalItemTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $seed = false;

    protected CbtKartuSoalItemModel $items;

    protected function setUp(): void
    {
        parent::setUp();
        $this->items = new CbtKartuSoalItemModel();
    }

    /** Kartu yang tidak ada: list kosong, count 0 — memvalidasi sintaks JOIN + COUNT. */
    public function testEmptyKartuReturnsNoRows(): void
    {
        $kartuId = 999999;

        $this->assertSame([], $this->items->getItemsWithTujuan($kartuId));
        $this->assertSame([], $this->items->getItemsWithTujuan($kartuId, ['tujuan_id' => 1, 'jenis_soal' => 2, 'q' => 'x'], 25, 0));
        $this->assertSame(0, $this->items->countItemsWithTujuan($kartuId));
        $this->assertSame(0, $this->items->countItemsWithTujuan($kartuId, ['tujuan_id' => 1, 'jenis_soal' => 2, 'q' => 'x']));
    }

    /** Kartu kosong: nomor pertama adalah 1, ringkasan nol. */
    public function testEmptyKartuSummaryDefaults(): void
    {
        $kartuId = 999999;

        $this->assertSame(1, $this->items->nextNomor($kartuId));
        $this->assertSame([], $this->items->getKartuSummary($kartuId));
        $this->assertSame(0, $this->items->getTotalEstimasi($kartuId));
        $this->assertSame(0, $this->items->getLinkedTujuanUniqueCount($kartuId));
    }

    /** Filter SQL konsisten dengan count-nya (kontrak pagination apiGetItems). */
    public function testFilterCountMatchesList(): void
    {
        $kartuId = 999999;
        $filters = ['tujuan_id' => 0, 'jenis_soal' => 0, 'q' => ''];

        $list = $this->items->getItemsWithTujuan($kartuId, $filters, 25, 0);
        $count = $this->items->countItemsWithTujuan($kartuId, $filters);

        $this->assertCount($count, $list);
    }
}
