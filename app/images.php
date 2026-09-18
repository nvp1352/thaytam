<?php
declare(strict_types=1);
/** Local image catalogue. All paths are controlled by the application. */
function offering_image(array $item): string {
    $images = [
        'Phần mềm bản quyền' => 'software-license.png',
        'MASAN eContract' => 'digital-contract.png',
        'Work+ · HRM+ · Info+' => 'team-workspace.png',
        'MASAN OncallCX' => 'customer-support.png',
        'Chuyển đổi số' => 'digital-transformation.png',
        'Hạ tầng & vận hành IT' => 'data-center.png',
        'Thiết kế sản phẩm số' => 'product-design.png',
        'Giải pháp theo ngành' => 'technology-team.png',
    ];
    return $images[$item['title'] ?? ''] ?? (($item['kind'] ?? '') === 'service' ? 'digital-transformation.png' : 'software-license.png');
}
