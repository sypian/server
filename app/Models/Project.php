<?php

namespace App\Models;

use GraphAware\Neo4j\OGM\Annotations as OGM;
use GraphAware\Neo4j\OGM\Common\Collection;

/**
 * @OGM\Node(label="Project")
 */
class Project
{
    /**
     * @var int
     *
     * @OGM\GraphId()
     */
    protected $id;

    /**
     * @var string
     *
     * @OGM\Property(type="string")
     */
    protected $name;

    /**
     * @var ProjectCategory[]
     *
     * @OGM\Relationship(relationshipEntity="ProjectCategory", type="BELONGS_TO", direction="INCOMING", collection=true, mappedBy="category")
     */
    protected $categories;

    public function __construct()
    {
        $this->categories = new Collection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName() :string
    {
        return $this->name;
    }

    /**
     * @return ProjectCategory[]|Collection
     */
    public function getCategories()
    {
        return $this->categories;
    }
}
