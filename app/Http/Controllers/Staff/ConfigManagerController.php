<?php

declare(strict_types=1);

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdateSiteConfigurationRequest;
use App\Services\SiteConfigurationService;
use Illuminate\Http\Request;

class ConfigManagerController extends Controller
{
    public function __construct(private readonly SiteConfigurationService $siteConfigurationService)
    {
    }

    /**
     * Display editable site configuration options.
     */
    public function index(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\View\View
    {
        $selectedFile = $request->string('file')->toString();
        $editableFiles = config('dashboard-settings.editable_files', []);

        if (!in_array($selectedFile, $editableFiles, true)) {
            $selectedFile = (string) ($editableFiles[0] ?? 'other');
        }

        return view('Staff.config-manager.index', [
            'editableFiles' => $editableFiles,
            'selectedFile' => $selectedFile,
            'options' => $this->siteConfigurationService->editableOptions($selectedFile),
        ]);
    }

    /**
     * Persist selected site configuration overrides.
     */
    public function update(UpdateSiteConfigurationRequest $request): \Illuminate\Http\RedirectResponse
    {
        $selectedFile = $request->string('selected_file')->toString();

        $this->siteConfigurationService->updateOverrides(
            $request->validated('settings'),
            $request->user()->id,
        );

        return to_route('staff.config_manager.index', ['file' => $selectedFile])
            ->with('success', 'Configuration has been updated successfully.');
    }
}
