<?php

declare(strict_types=1);

namespace Lwc\SettingsBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;

interface SettingsTypeInterface extends FormTypeInterface
{
    /**
     * This method should add a checkbox field to the form when adding a new setting's field.
     *
     * @param FormBuilderInterface $builder
     * @param $child
     * @param string|null $type
     * @param array $options
     *
     * @return $this
     */
    public function addWithDefaultCheckbox(FormBuilderInterface $builder, string $child, string $type = null, array $options = []): self;
}
