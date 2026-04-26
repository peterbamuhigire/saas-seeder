<?php
declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use App\UI\Components\{Button, DataTable, EmptyState, KpiStrip, StateBlock};
use App\UI\Form\{PasswordInput, TextInput, ValidationSummary};
use App\UI\Layout\{PageHeader, Shell};

$body = (new PageHeader('UI examples', 'SaaS Seeder'))->render()
    . '<div class="page-body"><div class="container-xl">'
    . KpiStrip::render([
        ['label' => 'Active tenants', 'value' => '12', 'trend' => 'Demo metric'],
        ['label' => 'Enabled modules', 'value' => '4'],
        ['label' => 'Open invites', 'value' => '8'],
        ['label' => 'Auth health', 'value' => 'OK'],
    ])
    . '<div class="card mt-3"><div class="card-body">'
    . ValidationSummary::render(['Example validation message'])
    . (new TextInput('tenant_name', 'Tenant name', 'Demo Franchise', 'Visible label and hint.', '', true))->render()
    . (new PasswordInput('password', 'Password', '', 'Use a strong password.', '', true))->render()
    . '</div></div>'
    . '<div class="card mt-3"><div class="card-body">'
    . DataTable::render(['Name', 'Status'], [['Name' => 'Auth', 'Status' => 'Core'], ['Name' => 'Reports', 'Status' => 'Disabled']])
    . '</div></div>'
    . '<div class="seeder-state-grid mt-3">'
    . StateBlock::render('loading', 'Loading state')
    . StateBlock::render('success', 'Success state')
    . StateBlock::render('error', 'Error state')
    . '</div>'
    . '<div class="card mt-3"><div class="card-body">'
    . EmptyState::render('Disabled module', 'This tenant does not have access to the selected module.', Button::link('Back to dashboard', '/dashboard.php'))
    . '</div></div>'
    . '</div></div>';

echo (new Shell('UI examples', $body))->render();
