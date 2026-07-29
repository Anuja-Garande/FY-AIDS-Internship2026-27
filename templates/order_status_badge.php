<?php
if (!isset($status)) return;

$status = strtolower(trim($status));

$config = [
    'pending'    => ['class' => 'bg-warning text-dark', 'icon' => 'fas fa-clock',         'label' => 'Pending'],
    'confirmed'  => ['class' => 'bg-info text-white',    'icon' => 'fas fa-check',         'label' => 'Confirmed'],
    'processing' => ['class' => 'bg-primary text-white', 'icon' => 'fas fa-spinner fa-spin','label' => 'Processing'],
    'shipped'    => ['class' => 'bg-secondary text-white','icon' => 'fas fa-truck',        'label' => 'Shipped'],
    'delivered'  => ['class' => 'bg-success text-white', 'icon' => 'fas fa-check-circle',  'label' => 'Delivered'],
    'cancelled'  => ['class' => 'bg-danger text-white',  'icon' => 'fas fa-times-circle',  'label' => 'Cancelled'],
    'returned'   => ['class' => 'bg-dark text-white',    'icon' => 'fas fa-undo',          'label' => 'Returned'],
    'refunded'   => ['class' => 'bg-info text-dark',     'icon' => 'fas fa-money-bill',    'label' => 'Refunded'],
];

$default = ['class' => 'bg-secondary text-white', 'icon' => 'fas fa-question-circle', 'label' => ucfirst($status)];
$info = $config[$status] ?? $default;
?>

<span class="badge <?= $info['class'] ?> d-inline-flex align-items-center gap-1 py-2 px-3" style="font-size:0.8rem; font-weight:600; border-radius:50px;">
    <i class="<?= $info['icon'] ?>"></i>
    <?= $info['label'] ?>
</span>
