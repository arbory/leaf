<?php

declare(strict_types=1);

namespace Arbory\Base\Admin\Form\Fields\Renderer;

use Arbory\Base\Files\ArboryImage;
use Arbory\Base\Html\Html;
use Arbory\Base\Html\Elements\Inputs\Input;
use Arbory\Base\Admin\Form\Fields\Renderer\Styles\Options\StyleOptionsInterface;

/**
 * Class ImageFieldRenderer.
 */
final class ImageFieldRenderer extends FileFieldRenderer
{
    /**
     * @return \Arbory\Base\Html\Elements\Element
     */
    public function render()
    {
        /** @var ArboryImage $image */
        $image = $this->field->getValue();

        $value = Html::div();

        $arboryFile = $this->getFile();

        if ($arboryFile) {
            $value->append(
                Html::image()
                    ->addAttributes([
                        'src' => $image->getUrl('thumbnail_small'),
                    ])
                    ->addClass('thumbnail')
            );
            $value->append($this->createFileDetails($arboryFile));
        }

        $value->append($this->getInput());

        return $value;
    }

    /**
     * @return Input
     */
    protected function getInput(): Input
    {
        $control = $this->getControl();
        $control = $this->configureControl($control);

        $element = $control->element();

        return $control->render($element);
    }

    /**
     * @param StyleOptionsInterface $options
     * @return StyleOptionsInterface
     */
    public function configure(StyleOptionsInterface $options): StyleOptionsInterface
    {
        // Use file Javascript
        $options->addClass('type-item type-file');

        return $options;
    }
}
