<?php
namespace Modules\Scraper\Services;

trait HtmlTable
{
    public function sourceTables()
    {
        // <table><thead><tr><th></th></tr></thead><tbody><tr><td></td></tr></tbody></table>
        $tables = [
            'width: 100%; border-collapse: collapse; font-size: 0.85rem; background-color: #ffffff; min-width: 480px;',
            'width: 100%; border-collapse: collapse; font-size: 0.8rem; background: #ffffff; min-width: 460px;',
            'width: 100%; border-collapse: collapse; font-size: 0.85rem; background: white; min-width: 520px; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);',
        ];
        $ths = [
            'padding: 12px 14px; text-align: left; font-weight: 600; background-color: #f9fafb; color: #1f2937; border-bottom: 1.5px solid #e5e7eb; font-size: 0.8rem;',
            'padding: 10px 12px; text-align: left; background-color: #f9f9fb; font-weight: 600; color: #1f2a3e; border-bottom: 1px solid #e2e8f0;',
            'padding: 10px 12px; text-align: left; background: #f3f4f6; color: #1f2937; border-bottom: 1px solid #d1d5db;',
            'padding: 11px 12px; text-align: left; background-color: #fef9e3; border-bottom: 2px solid #e2e2e2; font-weight: 600; color: #333;',
        ];
        $trs = [
            'border-bottom: 1px solid #f0f2f5;',
            'border-bottom: 1px solid #edf2f7;',
            'border-bottom: 1px solid #eef2f6;',
            'border-bottom: 1px solid #f0f0f0;',
            'border-bottom: none;',
        ];
        $tds = [
            'padding: 10px 12px;',
            'padding: 10px 12px; color: #1e293b;',
            'padding: 12px 14px; color: #111827; vertical-align: top;',
            'padding: 12px 12px; vertical-align: top;',
            'padding: 12px 12px; vertical-align: top; font-weight: 500;',
            'padding: 12px 12px; vertical-align: top; white-space: normal; word-break: break-word;',
        ];
        $spans = [
            'background: #e0f2fe; padding: 4px 10px; border-radius: 30px; font-size: 0.7rem; font-weight: 500;',
            'background: #e9eef5; padding: 4px 10px; border-radius: 30px; font-size: 0.7rem; font-weight: 500;',
            'background: #fed7aa; padding: 4px 10px; border-radius: 30px; font-size: 0.7rem; font-weight: 500;',
            'background: #e0f2fe; padding: 4px 10px; border-radius: 30px; font-size: 0.7rem; font-weight: 500;',
            'background: #fee2e2; padding: 4px 8px; border-radius: 40px; font-size: 0.7rem;',
            'background: #e6f7e6; padding: 4px 8px; border-radius: 40px; font-size: 0.7rem;',
            'background: #fff0db; padding: 4px 8px; border-radius: 40px; font-size: 0.7rem;'
        ];

        $divCard = 'max-width: 900px; margin: 0 auto; background: #ffffff; border-radius: 20px; padding: 20px 16px 32px; box-shadow: 0 4px 12px rgba(0,0,0,0.03), 0 1px 2px rgba(0,0,0,0.05);';
        $tableScrollContainer = 'overflow-x: auto; -webkit-overflow-scrolling: touch; margin: 1.2rem 0; border-radius: 14px;';
        $hr = 'margin: 28px 0 20px; border: 0; height: 1px; background: #e5e7eb;';
        $titles = [
            'font-size: 1.5rem; font-weight: 600; margin-bottom: 0.5rem; letter-spacing: -0.3px; color: #111827;',
            'font-weight: 500; margin: 8px 0 10px;',
            'font-weight: 500; margin-top: 10px;',
            'font-weight: 500; margin-bottom: 6px;',
        ];
        $desc = [
            'color: #4b5563; font-size: 0.85rem; margin-bottom: 1.5rem; border-left: 3px solid #9ca3af; padding-left: 12px;',
            'background: #f8fafc; padding: 12px 16px; border-radius: 14px; font-size: 0.75rem; color: #334155; margin: 16px 0;',
            'background: #fefce8; margin-top: 20px; padding: 10px 14px; border-radius: 14px; font-size: 0.75rem; border-left: 3px solid #eab308;',
            'font-size: 0.7rem; color: #5b677b; text-align: center; margin-top: 32px; border-top: 1px solid #eef2f5; padding-top: 20px;',
        ];
    }
}
