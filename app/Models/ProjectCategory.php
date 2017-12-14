<?php

namespace App\Models;

use GraphAware\Neo4j\OGM\Annotations as OGM;

/**
 *
 * @OGM\RelationshipEntity(type="BELONGS_TO")
 */
class ProjectCategory
{
    /**
     * @var int
     *
     * @OGM\GraphId()
     */
    protected $id;

    /**
     * @var Project
     *
     * @OGM\StartNode(targetEntity="Project")
     */
    protected $project;

    /**
     * @var Category
     *
     * @OGM\EndNode(targetEntity="Category")
     */
    protected $category;

    public function __construct(Project $project, Category $category)
    {
        $this->project = $project;
        $this->category = $category;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getProject(): Project
    {
        return $this->project;
    }
}
