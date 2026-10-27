<?php

namespace Tests\Feature;

use Tests\TestCase;

class AppShellUiTest extends TestCase
{
    public function test_dashboard_renders_searchable_workspace_navigation(): void
    {
        $this->get('/app')
            ->assertOk()
            ->assertSee('id="moduleSearch"', false)
            ->assertSee('id="moduleNavigation"', false)
            ->assertSee('id="workspaceIdentity"', false)
            ->assertSee('Papan Pemuka');
    }

    public function test_existing_module_pages_render_with_the_shared_workspace_shell(): void
    {
        $modules = [
            'ai-agents', 'team-ai', 'openbot', 'command-centre', 'prospects',
            'blast', 'boardroom', 'personal-ai', 'flows', 'ai-gateway', 'pejabat',
            'pos', 'crm', 'invoicing', 'marketplace', 'booking', 'omnichannel',
            'helpdesk', 'loyalty', 'affiliate', 'link', 'marketing', 'site',
            'inventory', 'cogs', 'manufacturing', 'logistics', 'scm', 'assets',
            'finance', 'payroll', 'hrms', 'projects', 'sign-docs', 'analytics',
            'brainhub', 'industry',
        ];

        foreach ($modules as $module) {
            $this->get("/app/{$module}")
                ->assertOk()
                ->assertSee('id="moduleSearch"', false);
        }
    }

    public function test_shell_exposes_accessible_landmarks_and_skip_link(): void
    {
        $this->get('/app')
            ->assertOk()
            ->assertSee('class="skip-link"', false)
            ->assertSee('href="#main-content"', false)
            ->assertSee('id="main-content"', false)
            ->assertSee('id="main-navigation"', false)
            ->assertSee('aria-label="Navigasi utama"', false)
            ->assertSee('aria-controls="main-navigation"', false);
    }

    public function test_every_visible_form_control_has_an_accessible_label(): void
    {
        $modules = [
            'command-centre', 'prospects', 'blast', 'boardroom', 'personal-ai',
            'ai-gateway', 'pos', 'inventory', 'cogs', 'brainhub', 'industry',
        ];

        foreach ($modules as $module) {
            $html = $this->get("/app/{$module}")->assertOk()->getContent();

            preg_match_all('/<(input|select|textarea)\b[^>]*>/i', $html, $matches);

            foreach ($matches[0] as $tag) {
                if (preg_match('/\btype=["\']hidden["\']/i', $tag)) {
                    continue;
                }

                preg_match('/\bid=["\']([^"\']+)["\']/i', $tag, $idMatch);
                $id = $idMatch[1] ?? null;

                $hasAria = preg_match('/\baria-label(?:ledby)?=/i', $tag) === 1;
                $hasLabelFor = $id !== null
                    && preg_match('/<label[^>]*\bfor=["\']' . preg_quote($id, '/') . '["\']/i', $html) === 1;
                $hasWrappingLabel = preg_match(
                    '/<label\b[^>]*>\s*' . preg_quote($tag, '/') . '/i',
                    $html
                ) === 1;

                $this->assertTrue(
                    $hasAria || $hasLabelFor || $hasWrappingLabel,
                    "Kawalan tanpa label aksesibel dalam modul {$module}: {$tag}"
                );
            }
        }
    }

    public function test_labels_are_bound_to_their_controls(): void
    {
        $modules = [
            'boardroom', 'personal-ai', 'ai-gateway', 'prospects', 'blast', 'pos',
        ];

        foreach ($modules as $module) {
            $html = $this->get("/app/{$module}")->assertOk()->getContent();

            preg_match_all('/<label\b([^>]*)>/i', $html, $labels);
            foreach ($labels[1] as $attrs) {
                if (!str_contains($attrs, 'class="label"')) {
                    continue;
                }

                $this->assertMatchesRegularExpression(
                    '/\bfor=["\'][^"\']+["\']/i',
                    $attrs,
                    "Label tanpa atribut for= dalam modul {$module}: <label{$attrs}>"
                );
            }
        }
    }
}
