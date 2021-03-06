<?php declare (strict_types=1);

class SomePresenter
{
    public function createForm()
    {
        $form = new Form;

        $form->onSuccess[] = $this->someMethod;

        return $form;
    }

    public function createNetteForm()
    {
        $form = new \Nette\Application\UI\Form;

        $form->onSuccess[] = $this->someMethod;

        return $form;
    }
}
