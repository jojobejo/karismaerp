<?php
$pagination = $pagination ?? [];
$queryParams = $query_params ?? [];
$totalPages = (int)($pagination['total_pages'] ?? 1);
$currentPage = (int)($pagination['current_page'] ?? 1);

if ($totalPages > 1):
    $pageNumbers = [1, $totalPages];
    for ($page = max(1, $currentPage - 2); $page <= min($totalPages, $currentPage + 2); $page++) {
        $pageNumbers[] = $page;
    }
    $pageNumbers = array_values(array_unique($pageNumbers));
    sort($pageNumbers);
?>
<div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap border-top">
    <small class="text-muted mb-2 mb-md-0">
        Menampilkan <?= (int)$pagination['from'] ?>–<?= (int)$pagination['to'] ?> dari <?= (int)$pagination['total_rows'] ?> data
    </small>
    <nav aria-label="Navigasi halaman penyelesaian konsinyasi">
        <ul class="pagination pagination-sm mb-0">
            <?php
            $previousParams = array_merge($queryParams, ['page_penyelesaian' => max(1, $currentPage - 1)]);
            $nextParams = array_merge($queryParams, ['page_penyelesaian' => min($totalPages, $currentPage + 1)]);
            ?>
            <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= site_url('purchasing/konsinyasi') . '?' . http_build_query($previousParams) ?>">Sebelumnya</a>
            </li>
            <?php $lastNumber = 0; foreach ($pageNumbers as $pageNumber): ?>
                <?php if ($lastNumber > 0 && $pageNumber > $lastNumber + 1): ?>
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                <?php endif; ?>
                <?php $numberParams = array_merge($queryParams, ['page_penyelesaian' => $pageNumber]); ?>
                <li class="page-item <?= $pageNumber === $currentPage ? 'active' : '' ?>">
                    <a class="page-link" href="<?= site_url('purchasing/konsinyasi') . '?' . http_build_query($numberParams) ?>"><?= $pageNumber ?></a>
                </li>
            <?php $lastNumber = $pageNumber; endforeach; ?>
            <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                <a class="page-link" href="<?= site_url('purchasing/konsinyasi') . '?' . http_build_query($nextParams) ?>">Berikutnya</a>
            </li>
        </ul>
    </nav>
</div>
<?php endif; ?>
