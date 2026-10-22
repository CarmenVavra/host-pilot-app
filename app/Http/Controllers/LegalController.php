<?php

namespace App\Http\Controllers;

use App\Support\LegalInfo;
use Illuminate\View\View;

/**
 * Public legal pages: Impressum and Datenschutzerklärung.
 */
class LegalController extends Controller
{
    public function imprint(): View
    {
        $legal = LegalInfo::fromConfig();

        return view('legal.imprint', compact('legal'));
    }

    public function privacy(): View
    {
        $legal = LegalInfo::fromConfig();

        return view('legal.privacy', compact('legal'));
    }
}
