<?php

namespace Concrete\Attribute\Topics;

use Concrete\Core\Api\ApiResourceValueInterface;
use Concrete\Core\Api\Attribute\OpenApiSpecifiableInterface;
use Concrete\Core\Api\Attribute\SupportsAttributeValueFromJsonInterface;
use Concrete\Core\Api\Fractal\Transformer\TopicTreeNodeTransformer;
use Concrete\Core\Api\OpenApi\SpecProperty;
use Concrete\Core\Api\Resources;
use Concrete\Core\Attribute\Controller as AttributeTypeController;
use Concrete\Core\Attribute\FontAwesomeIconFormatter;
use Concrete\Core\Attribute\SimpleTextExportableAttributeInterface;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\TopicsSettings;
use Concrete\Core\Entity\Attribute\Value\Value\SelectedTopic;
use Concrete\Core\Entity\Attribute\Value\Value\TopicsValue;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Search\ItemList\Database\AttributedItemList;
use Concrete\Core\Tree\Node\Node;
use Concrete\Core\Tree\Node\Node as TreeNode;
use Concrete\Core\Tree\Node\Type\Category;
use Concrete\Core\Tree\Node\Type\Topic;
use Concrete\Core\Tree\Tree;
use Concrete\Core\Tree\Type\Topic as TopicTree;
use Concrete\Core\Utility\Service\Xml;
use Core;
use Database;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceInterface;

class Controller extends AttributeTypeController implements
    SimpleTextExportableAttributeInterface,
    OpenApiSpecifiableInterface,
    SupportsAttributeValueFromJsonInterface,
    ApiResourceValueInterface
{
    public $akTopicParentNodeID;
    public $akTopicTreeID;

    public $helpers = ['form'];

    protected $searchIndexFieldDefinition = ['type' => 'text', 'options' => ['default' => null, 'notnull' => false]];
    private $akTopicAllowMultipleValues = true;

    public function getIconFormatter()
    {
        return new FontAwesomeIconFormatter('tag');
    }

    public function getAttributeValueClass()
    {
        return TopicsValue::class;
    }

    public function filterByAttribute(AttributedItemList $list, $value, $comparison = '=')
    {
        if (is_array($value)) {
            $topics = $value;
        } else {
            $topics = [$value];
        }

        $expressions = [];
        $qb = $list->getQueryObject();
        $expr = $qb->expr();
        foreach ($topics as $value) {
            if ($value instanceof TreeNode) {
                $topic = $value;
            } else {
                $topic = Node::getByID((int) $value);
            }
            if (is_object($topic) && (
                    $topic instanceof \Concrete\Core\Tree\Node\Type\Topic ||
                    $topic instanceof Category)) {
                $column = 'ak_' . $this->attributeKey->getAttributeKeyHandle();
                $expressions[] = $expr->like($column, $qb->createNamedParameter('%||' . $topic->getTreeNodeDisplayPath() . '||%'));
                $expressions[] = $expr->like($column, $qb->createNamedParameter('%||' . $topic->getTreeNodeDisplayPath() . '/%'));
            }
        }

        $qb->andWhere(call_user_func_array([$expr, 'orX'], $expressions));
    }

    public function saveKey($data)
    {
        $type = $this->getAttributeKeySettings();
        $data += [
            'akTopicParentNodeID' => null,
            'akTopicTreeID' => null,
        ];
        $akTopicParentNodeID = $data['akTopicParentNodeID'];
        $akTopicTreeID = $data['akTopicTreeID'];
        if (isset($data['akTopicAllowMultipleValues']) && $data['akTopicAllowMultipleValues'] == 1) {
            $akTopicAllowMultipleValues = 1;
        } else {
            $akTopicAllowMultipleValues = 0;
        }
        if ($akTopicParentNodeID) {
            $type->setParentNodeID($akTopicParentNodeID);
        }
        if ($akTopicTreeID) {
            $type->setTopicTreeID($akTopicTreeID);
        }
        $type->setAllowMultipleValues((bool) $akTopicAllowMultipleValues);

        return $type;
    }

    /**
     * @deprecated
     */
    public function getSelectedOptions()
    {
        return $this->attributeValue->getValueObject()->getSelectedTopics();
    }

    public function exportValue(\SimpleXMLElement $akn)
    {
        $xml = $this->app->make(Xml::class);
        $avn = $akn->addChild('topics');
        $nodes = $this->attributeValue->getValue();
        foreach ($nodes as $topic) {
            $xml->createChildElement($avn, 'topic', $topic->getTreeNodeDisplayPath());
        }
    }

    public function importValue(\SimpleXMLElement $akn)
    {
        $selected = [];
        if (isset($akn->topics)) {
            foreach ($akn->topics->topic as $topicPath) {
                $selected[] = (string) $topicPath;
            }

            return $this->createAttributeValue($selected);
        }
    }

    /**
     * @deprecated
     *
     * @param mixed $akTopicParentNodeID
     * @param mixed $akTopicTreeID
     */
    public function setNodes($akTopicParentNodeID, $akTopicTreeID)
    {
        /** @var TopicsSettings $type */
        $type = $this->getAttributeKey()->getAttributeKeySettings();
        $type->setParentNodeID($akTopicParentNodeID);
        $type->setTopicTreeID($akTopicTreeID);
        $this->entityManager->persist($type);
        $this->entityManager->flush();
    }

    public function createAttributeValue($nodes)
    {
        $selected = [];
        $this->load();
        $tree = Tree::getByID($this->akTopicTreeID);
        if ($nodes instanceof Topic) {
            $selected[] = $nodes->getTreeNodeID();
        } else {
            foreach ($nodes as $topicPath) {
                if ($topicPath instanceof Topic) {
                    $selected[] = $topicPath->getTreeNodeID();
                } else {
                    $node = $tree->getNodeByDisplayPath($topicPath);
                    if (is_object($node)) {
                        $selected[] = $node->getTreeNodeID();
                    }
                }
            }
        }

        $topicsValue = new TopicsValue();
        foreach ($selected as $treeNodeID) {
            $topicsValueNode = new SelectedTopic();
            $topicsValueNode->setAttributeValue($topicsValue);
            $topicsValueNode->setTreeNodeID($treeNodeID);
            $topicsValue->getSelectedTopics()->add($topicsValueNode);
        }

        return $topicsValue;
    }

    public function exportKey($key)
    {
        $this->load();
        $tree = Tree::getByID($this->akTopicTreeID);
        $node = Node::getByID($this->akTopicParentNodeID);
        $path = $node->getTreeNodeDisplayPath();
        $treeNode = $key->addChild('tree');
        $treeNode->addAttribute('name', $tree->getTreeName());
        $treeNode->addAttribute('path', $path);
        $treeNode->addAttribute('allow-multiple-values', $this->akTopicAllowMultipleValues);

        return $key;
    }

    public function importKey(\SimpleXMLElement $key)
    {
        $type = $this->getAttributeKeySettings();
        $name = (string) $key->tree['name'];
        $tree = \Concrete\Core\Tree\Type\Topic::getByName($name);
        $node = $tree->getNodeByDisplayPath((string) $key->tree['path']);
        $allowMultipleValues = $key->tree['allow-multiple-values'];
        $type->setTopicTreeID($tree->getTreeID());
        $type->setParentNodeID($node->getTreeNodeID());
        $type->setAllowMultipleValues(((string) $allowMultipleValues) == '1' ? true : false);

        return $type;
    }

    public function form($additionalClass = false)
    {
        $this->load();
        $valueIDs = [];
        if ($this->request->isPost()) {
            $valueIDs = (array) $this->request->request->get('topics_' . $this->attributeKey->getAttributeKeyID());
        } else {
            if (is_object($this->attributeValue)) {
                $valueIDs = [];
                $valueObject = $this->attributeValue->getValueObject();
                if ($valueObject) {
                    foreach ($valueObject->getSelectedTopics() as $value) {
                        $valueID = $value->getTreeNodeID();
                        $withinParentScope = false;
                        $nodeObj = TreeNode::getByID($value->getTreeNodeID());
                        if (is_object($nodeObj)) {
                            $parentNodeArray = $nodeObj->getTreeNodeParentArray();
                            // check to see if selected node is still within parent scope, in case it has been changed.
                            foreach ($parentNodeArray as $parent) {
                                if ($parent->treeNodeID == $this->akTopicParentNodeID) {
                                    $withinParentScope = true;
                                    break;
                                }
                            }
                            if ($withinParentScope) {
                                $valueIDs[] = $valueID;
                            }
                        }
                    }
                }
            }
        }
        $this->set('valueIDs', implode(',', $valueIDs));
        $this->set('valueIDArray', $valueIDs);
        $ak = $this->getAttributeKey();
        $this->set('akID', $ak->getAttributeKeyID());
        $this->set('parentNode', $this->akTopicParentNodeID);
        $this->set('treeID', $this->akTopicTreeID);
        $this->set('allowMultipleValues', $this->akTopicAllowMultipleValues);
    }

    public function searchForm($list)
    {
        $list->filterByAttribute($this->attributeKey->getAttributeKeyHandle(), $this->request('treeNodeID'));

        return $list;
    }

    public function getSearchIndexValue()
    {
        $str = '||';
        $nodeKeys = $this->attributeValue->getValue();
        if ($nodeKeys) {
            foreach ($nodeKeys as $nodeObj) {
                $str .= $nodeObj->getTreeNodeDisplayPath() . '||';
            }
        }

        if ($str == "||") {
            return '';
        }

        return $str;
    }

    public function search()
    {
        $this->load();
        $tree = TopicTree::getByID(Core::make('helper/security')->sanitizeInt($this->akTopicTreeID));
        $this->set('tree', $tree);
        $treeNodeID = $this->request('treeNodeID');
        if (!$treeNodeID) {
            $treeNodeID = $this->akTopicParentNodeID;
        }
        $this->set('selectedNode', $treeNodeID);
        $this->set('attributeKey', $this->attributeKey);
    }

    public function createAttributeValueFromRequest()
    {
        $sh = Core::make('helper/security');
        $av = new TopicsValue();
        $cleanIDs = [];
        $topicsArray = $this->request->request->get('topics_' . $this->attributeKey->getAttributeKeyID());
        if (is_array($topicsArray) && count($topicsArray) > 0) {
            foreach ($topicsArray as $topicID) {
                $cleanIDs[] = $sh->sanitizeInt($topicID);
            }
            foreach ($cleanIDs as $topID) {
                $topic = new SelectedTopic();
                $topic->setAttributeValue($av);
                $topic->setTreeNodeID($topID);
                $av->getSelectedTopics()->add($topic);
            }
        }

        return $av;
    }

    public function type_form()
    {
        $this->load();
        $tt = new TopicTree();
        $defaultTree = $tt->getDefault();
        $topicTreeList = $tt->getList();
        $tree = $tt->getByID(Core::make('helper/security')->sanitizeInt($this->akTopicTreeID));
        if (!$tree) {
            $tree = $defaultTree;
        }
        $this->set('tree', $tree);
        $trees = [];
        if (is_object($defaultTree)) {
            $trees[] = $defaultTree;
            foreach ($topicTreeList as $ctree) {
                if ($ctree->getTreeID() != $defaultTree->getTreeID()) {
                    $trees[] = $ctree;
                }
            }
        }
        $this->set('trees', $trees);
        $this->set('parentNode', $this->akTopicParentNodeID);
        $this->set('allowMultipleValues', $this->akTopicAllowMultipleValues);
    }

    public function validateKey($data = false)
    {
        if ($data == false) {
            $data = $this->post();
        }

        $e = $this->app->make('error');

        if (!$data['akTopicParentNodeID'] || !$data['akTopicTreeID']) {
            $e->add(t('You must specify a valid topic tree parent node ID and topic tree ID.'));
        }

        return $e;
    }

    public function validateValue()
    {
        $val = $this->getAttributeValue()->getValue();

        return is_array($val) && count($val) > 0;
    }

    public function validateForm($p)
    {
        $topicsArray = $_POST['topics_' . $this->attributeKey->getAttributeKeyID()] ?? null;

        return is_array($topicsArray) && count($topicsArray) > 0;
    }

    public function getTopicParentNode()
    {
        $this->load();

        return $this->akTopicParentNodeID;
    }

    public function getTopicTreeID()
    {
        $this->load();

        return $this->akTopicTreeID;
    }

    public function allowMultipleValues()
    {
        $this->load();

        return $this->akTopicAllowMultipleValues;
    }

    public function duplicateKey($newAK)
    { // TODO this is going to need some work to function with the child options table...
        $this->load();
        $db = Database::get();
        $db->Replace(
            'atTopicSettings',
            [
                'akID' => $newAK->getAttributeKeyID(),
                'akTopicParentNodeID' => $this->akTopicParentNodeID,
                'akTopicTreeID' => $this->akTopicTreeID,
                'akTopicAllowMultipleValues' => $this->akTopicAllowMultipleValues,
            ],
            ['akID'],
            true
        );
    }

    public function getAttributeKeySettingsClass()
    {
        return TopicsSettings::class;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\SimpleTextExportableAttributeInterface::getAttributeValueTextRepresentation()
     */
    public function getAttributeValueTextRepresentation()
    {
        $result = '';
        $value = $this->getAttributeValueObject();
        if ($value !== null) {
            $topics = $value->getSelectedTopics();
            if (!empty($topics)) {
                $ids = [];
                foreach ($topics as $topic) {
                    /* @var \\Concrete\Core\Entity\Attribute\Value\Value\SelectedTopic $topic */
                    $ids[] = 'tid:' . $topic->getTreeNodeID();
                }
                $result = implode('|', $ids);
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Attribute\SimpleTextExportableAttributeInterface::updateAttributeValueFromTextRepresentation()
     */
    public function updateAttributeValueFromTextRepresentation($textRepresentation, ErrorList $warnings)
    {
        $value = $this->getAttributeValueObject();
        $textRepresentation = trim($textRepresentation);
        if ($textRepresentation === '') {
            if ($value !== null) {
                $value->getSelectedTopics()->clear();
            }
        } elseif (!preg_match('/^tid:\d+(,tid:\d+)*$/', $textRepresentation)) {
            $warnings->add(t('"%1$s" does not represent a valid value for the Topics attribute with handle %2$s', $textRepresentation, $this->attributeKey->getAttributeKeyHandle()));
        } else {
            if (!isset($this->akTopicParentNodeID)) {
                $this->load();
            }
            $initialized = false;
            preg_match_all('/tid:(\d+)\b/', $textRepresentation, $matches);
            $nodeIDs = array_unique(array_map('intval', $matches[1]));
            foreach ($nodeIDs as $nodeID) {
                $node = TreeNode::getByID($nodeID);
                if ($node === null) {
                    $warnings->add(t('"%1$s" is not a valid node identifier for the Topics attribute with handle %2$s', $nodeID, $this->attributeKey->getAttributeKeyHandle()));
                    continue;
                }
                $withinParentScope = false;
                $parentNodes = $node->getTreeNodeParentArray();
                foreach ($parentNodes as $parentNode) {
                    if ($parentNode->getTreeNodeID() == $this->akTopicParentNodeID) {
                        $withinParentScope = true;
                        break;
                    }
                }
                if ($withinParentScope === false) {
                    $warnings->add(t('The Topic node with ID "%1$s" is not a child of the root node of the Topics attribute with handle %2$s', $nodeID, $this->attributeKey->getAttributeKeyHandle()));
                    continue;
                }
                if ($initialized === false) {
                    $initialized = true;
                    if ($value === null) {
                        $value = new TopicsValue();
                    } else {
                        $value->getSelectedTopics()->clear();
                    }
                }
                $topic = new SelectedTopic();
                $topic->setAttributeValue($value);
                $topic->setTreeNodeID($nodeID);
                $value->getSelectedTopics()->add($topic);
            }
        }

        return $value;
    }

    protected function load()
    {
        $ak = $this->getAttributeKey();
        if (!is_object($ak)) {
            return false;
        }

        /** @var TopicsSettings $type */
        $type = $ak->getAttributeKeySettings();
        if (is_object($type)) {
            $this->akTopicParentNodeID = $type->getParentNodeID();
            $this->akTopicTreeID = $type->getTopicTreeID();
            $this->akTopicAllowMultipleValues = $type->allowMultipleValues();
        }
    }

    public function getOpenApiSpecProperty(Key $key): SpecProperty
    {
        return new SpecProperty(
            $key->getAttributeKeyHandle(),
            $key->getAttributeKeyDisplayName(),
            'array',
            null,
            ['type' => 'integer'],
        );
    }

    public function createAttributeValueFromNormalizedJson($json)
    {
        $av = new TopicsValue();
        foreach ((array) $json as $topicId) {
            $topicId = (int) $topicId;
            if ($topicId) {
                $topic = new SelectedTopic();
                $topic->setAttributeValue($av);
                $topic->setTreeNodeID($topicId);
                $av->getSelectedTopics()->add($topic);
            }
        }
        return $av;
    }

    public function getApiValueResource(): ?ResourceInterface
    {
        $value = $this->getAttributeValueObject();
        if ($value) {
            /**
             * @var $value TopicsValue
             */
            $topics = [];
            $selectedTopics = $value->getSelectedTopics();
            foreach ($selectedTopics as $selectedTopic) {
                /**
                 * @var $selectedTopic SelectedTopic
                 */
                $node = Node::getByID($selectedTopic->getTreeNodeID());
                if ($node instanceof Topic) {
                    $topics[] = $node;
                }
            }
            return new Collection($topics, new TopicTreeNodeTransformer(), Resources::RESOURCE_TOPICS);
        }
        return null;
    }

}
