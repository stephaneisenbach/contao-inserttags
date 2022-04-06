<?php

declare(strict_types=1);

/*
 * This file is part of terminal42/contao-inserttags.
 *
 * (c) terminal42
 *
 * @license MIT
 */

namespace Terminal42\InsertTagsBundle\EventListener;

use Contao\Backend;
use Contao\DataContainer;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

class DcaListener
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * On load callback.
     */
    public function onLoadCallback(DataContainer $dc): void
    {
        // Disable rich text editor if checkbox is set
        if ('edit' === Input::get('act')) {
            $disableRTE = $this->connection->fetchOne("SELECT disableRTE {$dc->table} WHERE id=?", [$dc->id]);

            if ($disableRTE) {
                unset($GLOBALS['TL_DCA'][$dc->table]['fields']['replacement']['eval']['rte']);
            }
        }
    }

    /**
     * On label callback.
     */
    public function onLabelCallback(array $row): string
    {
        $label = $row['tag'];

        if (\strlen($row['description']) > 0) {
            $label .= sprintf('<span class="tl_gray">(%s)</span>', $row['description']);
        }

        return '<img width="18" height="18" style="margin-left:0" alt="" src="bundles/terminal42inserttags/page.gif"> '.$label;
    }

    /**
     * On paste button callback.
     */
    public function onPasteButtonCallback(DataContainer $dc, array $row, string $table, bool $cr, array $clipboard): string
    {
        $imagePasteAfter = Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id']));
        $imagePasteInto = Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id']));

        if (0 === $row['id']) {
            return $cr ? Image::getHtml('pasteinto_.gif').' ' : '<a href="'.Backend::addToUrl('act='.$clipboard['mode'].'&amp;mode=2&amp;pid='.$row['id'].'&amp;id='.$clipboard['id']).'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteinto'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteInto.'</a> ';
        }

        return ('cut' === $clipboard['mode'] && $clipboard['id'] === $row['id']) || $cr ? Image::getHtml('pasteafter_.gif').' ' : '<a href="'.Backend::addToUrl('act='.$clipboard['mode'].'&amp;mode=1&amp;pid='.$row['id'].'&amp;id='.$clipboard['id']).'" title="'.StringUtil::specialchars(sprintf($GLOBALS['TL_LANG'][$dc->table]['pasteafter'][1], $row['id'])).'" onclick="Backend.getScrollOffset();">'.$imagePasteAfter.'</a> ';
    }
}