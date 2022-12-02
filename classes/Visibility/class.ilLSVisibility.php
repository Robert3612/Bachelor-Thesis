<?php

declare(strict_types=1);

/**
 * A PostCondition does restrict the progression of a user through the learning sequence.
 * Thus, instead of saying "You may only _visit_ this object if you did this",
 * a PostCondition says "you may only _leave_ this object if you did this".
 *
 * LSPostConditions are being applied by the LearningSequenceConditionController.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSVisibility
{
    /**
     * @var int
     */
    protected $ref_id;

    /**
     * @var string
     */
    protected $visibility;

    /**
     * @var mixed
     */
    protected $value;

    protected $pre;

    protected $vis;

    public function __construct(
        int $ref_id,
        string $visibility,
        string $pre,
        bool $vis,
        $value = null
    ) {
        $this->ref_id = $ref_id;
        $this->pre = $pre;
        $this->visibility = $visibility;
        $this->vis = $vis;
        $this->value = $value;
    }

    public function getRefId() : int
    {
        return $this->ref_id;
    }

    public function getVis() : bool
    {
        if($this->vis != null) {
            return $this->vis;
        }
        else{
            return false;
        }
    }

    public function withVis(bool $pre) : ilLSVisibility
    {
        $clone = clone $this;
        $clone->vis = $pre;
        return $clone;
    }


    public function getVisibilityOperator() : string
    {
        return $this->visibility;
    }

    public function getPre() : string
    {
        return $this->pre;
    }

    public function withPre(string $pre) : ilLSVisibility
    {
        $clone = clone $this;
        $clone->pre = $pre;
        return $clone;
    }


    public function withVisibilityOperator(string $visibility) : ilLSVisibility
    {
        $clone = clone $this;
        $clone->visibility = $visibility;
        return $clone;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function withValue($value) : ilLSPostCondition
    {

        $clone = clone $this;
        $clone->value = $value;
        return $clone;
    }
}
