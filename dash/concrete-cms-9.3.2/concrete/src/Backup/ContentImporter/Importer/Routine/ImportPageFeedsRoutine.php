<?php
namespace Concrete\Core\Backup\ContentImporter\Importer\Routine;

use Concrete\Core\Page\Feed;
use Concrete\Core\Utility\Service\Xml;

class ImportPageFeedsRoutine extends AbstractRoutine
{
    public function getHandle()
    {
        return 'page_feeds';
    }

    public function import(\SimpleXMLElement $sx)
    {
        if (isset($sx->pagefeeds)) {
            $xml = app(Xml::class);
            foreach ($sx->pagefeeds->feed as $f) {
                $feed = Feed::getByHandle((string) $f->handle);
                $inspector = \Core::make('import/value_inspector');
                if (!is_object($feed)) {
                    $feed = new \Concrete\Core\Entity\Page\Feed();
                }
                if ($f->parent) {
                    $result = $inspector->inspect((string) $f->parent);
                    $parent = $result->getReplacedValue();
                    $feed->setParentID($parent);
                }
                $feed->setTitle((string) $f->title);
                $feed->setDescription((string) $f->description);
                $feed->setHandle((string) $f->handle);
                $feed->setIncludeAllDescendents($xml->getBool($f->descendents));
                $feed->setDisplayAliases($xml->getBool($f->aliases));
                $feed->setDisplayFeaturedOnly($xml->getBool($f->featured));
                if ($f->pagetype) {
                    $result = $inspector->inspect((string) $f->pagetype);
                    $pagetype = $result->getReplacedValue();
                    $feed->setPageTypeID($pagetype);
                }
                $contentType = $f->contenttype;
                $type = (string) $contentType['type'];
                if ($type == 'description') {
                    $feed->displayShortDescriptionContent();
                } elseif ($type == 'area') {
                    $feed->displayAreaContent((string) $contentType['handle']);
                }
                $feed->save();
            }
        }
    }
}
