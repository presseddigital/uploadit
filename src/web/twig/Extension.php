<?php
namespace presseddigital\uploadit\web\twig;

use presseddigital\uploadit\Uploadit;
use presseddigital\uploadit\web\twig\UploaditVariable;

use Craft;
use Twig\Extension\{AbstractExtension, GlobalsInterface};
use Twig\{ExpressionParser, TwigFilter, TwigFunction, Markup as TwigMarkup};

use DateTime, DateInterval;

class Extension extends AbstractExtension implements GlobalsInterface
{
    // Twig Globals {{ global }}
    // =========================================================================

    public function getGlobals(): array
    {
        if (!Uploadit::$variable)
        {
            Uploadit::$variable = new UploaditVariable();
        }

        return [
            'uploadit' => Uploadit::$variable,
        ];
    }

    // Twig Filters {{ var|filter }}
    // =========================================================================

    public function getFilters(): array
    {
        return [
            // new TwigFilter('fieldHandle', [Fields::class, 'nameToHandle']),
        ];
    }

    // Twig Functions {{ function(var) }}
    // =========================================================================

    public function getFunctions()
    {
        return [
            // new TwigFunction('fieldHandle', [Fields::class, 'nameToHandle']),
        ];
    }

    // Public Methods
    // =========================================================================

}
