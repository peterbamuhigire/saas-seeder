<?php

declare(strict_types=1);

namespace Tests\Unit\UI;

use App\UI\Components\Button;
use App\UI\Components\DataTable;
use App\UI\Components\EmptyState;
use App\UI\Components\FilterBar;
use App\UI\Components\KpiStrip;
use App\UI\Components\ModuleBadge;
use App\UI\Components\Pagination;
use App\UI\Components\StateBlock;
use App\UI\Components\TenantBadge;
use App\UI\Form\Checkbox;
use App\UI\Form\FormAlert;
use App\UI\Form\FormRenderer;
use App\UI\Form\PasswordInput;
use App\UI\Form\TextInput;
use App\UI\Form\ValidationSummary;
use App\UI\Layout\Breadcrumbs;
use App\UI\Layout\Footer;
use App\UI\Layout\PageHeader;
use App\UI\Layout\Shell;
use App\UI\Layout\TenantContext;
use App\UI\Layout\Topbar;
use App\UI\Navigation\ActiveRoute;
use App\UI\Navigation\MenuItem;
use App\UI\Navigation\MenuRegistry;
use App\UI\Navigation\MenuRenderer;
use App\UI\Support\Escaper;
use PHPUnit\Framework\TestCase;

final class ComponentLibraryTest extends TestCase
{
    public function testDisplayComponentsEscapeUntrustedValues(): void
    {
        self::assertStringContainsString('&lt;Admin&gt;', Button::link('<Admin>', '/x?y="z"', 'primary'));
        self::assertStringContainsString('&lt;script&gt;', DataTable::render(['Name'], [['Name' => '<script>']]));
        self::assertStringContainsString('&lt;Title&gt;', EmptyState::render('<Title>', 'None', '<button>Safe caller HTML</button>'));
        self::assertStringContainsString('name="q&quot;x"', FilterBar::search('q"x'));
        self::assertStringContainsString('&lt;Trend&gt;', KpiStrip::render([['label' => 'Users', 'value' => '10', 'trend' => '<Trend>']]));
        self::assertStringContainsString('bg-green-lt', ModuleBadge::render('AUTH', true));
        self::assertStringContainsString('bg-red-lt', ModuleBadge::render('BILLING', false));
        self::assertStringContainsString('Page 1 of 1', Pagination::render(0, 0));
        self::assertStringContainsString('alert-danger', StateBlock::render('error', '<failed>'));
        self::assertStringContainsString('alert-secondary', StateBlock::render('unknown', 'Waiting'));
        self::assertStringContainsString('&lt;Tenant&gt;', TenantBadge::render('<Tenant>'));
        self::assertSame('&lt;b&gt;&quot;', Escaper::html('<b>"'));
    }

    public function testFormComponentsRenderAccessibleStates(): void
    {
        $text = new TextInput('display name', 'Display name', '<Peter>', 'Shown publicly', 'Required', true);
        $password = new PasswordInput('password', 'Password', '', 'At least 12 characters', '', true);
        $checkbox = new Checkbox('enabled', 'Enabled', '1');
        $form = (new FormRenderer())->render([$text, $password, $checkbox], '/save?x="1"');

        self::assertStringContainsString('id="field-display-name"', $form);
        self::assertStringContainsString('aria-invalid="true"', $form);
        self::assertStringContainsString('aria-required="true"', $form);
        self::assertStringContainsString(' checked', $form);
        self::assertStringContainsString('/save?x=&quot;1&quot;', $form);
        self::assertStringContainsString('aria-live="polite"', FormAlert::render('<Bad>'));
        self::assertSame('', ValidationSummary::render([]));
        self::assertStringContainsString('&lt;Issue&gt;', ValidationSummary::render(['<Issue>']));
    }

    public function testLayoutComponentsProvideSemanticStructure(): void
    {
        self::assertSame('', Breadcrumbs::render([]));
        $breadcrumbs = Breadcrumbs::render([
            ['label' => 'Home', 'href' => '/'],
            ['label' => '<Current>'],
        ]);
        self::assertStringContainsString('aria-current="page"', $breadcrumbs);
        self::assertStringContainsString('&lt;Current&gt;', $breadcrumbs);

        $header = (new PageHeader('<Users>', 'Admin', [['label' => 'Home', 'href' => '/']]))->render();
        self::assertStringContainsString('&lt;Users&gt;', $header);
        self::assertStringContainsString('page-pretitle', $header);
        self::assertStringNotContainsString('page-pretitle', (new PageHeader('Users'))->render());
        self::assertStringContainsString('&lt;Footer&gt;', Footer::render('<Footer>'));
        self::assertStringContainsString('href="#main-body"', Topbar::skipLink());

        $shell = (new Shell('<Title>', '<section>Trusted body</section>', 'member'))->render();
        self::assertStringContainsString('&lt;Title&gt;', $shell);
        self::assertStringContainsString('id="main-body"', $shell);
        self::assertStringContainsString('data-panel="member"', $shell);

        $tenant = new TenantContext(3, 'Acme', 'ACME', 'UGX');
        self::assertSame(3, $tenant->id);
        self::assertSame('Africa/Kampala', $tenant->timezone);
    }

    public function testNavigationMatchesExactAndPatternRoutes(): void
    {
        $item = new MenuItem('Users', '/users.php', 'admin', activePatterns: ['/users/*']);
        self::assertTrue(ActiveRoute::matches($item, '/users.php'));
        self::assertTrue(ActiveRoute::matches($item, '/users/12'));
        self::assertFalse(ActiveRoute::matches($item, '/settings.php'));

        $defaults = MenuRegistry::defaults();
        self::assertCount(4, $defaults);
        $html = (new MenuRenderer())->render($defaults, 'admin', '/dashboard.php');
        self::assertStringContainsString('nav-link active', $html);
        self::assertStringNotContainsString('My dashboard', $html);
    }
}
