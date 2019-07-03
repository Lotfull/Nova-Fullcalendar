<?php

namespace Extremebox\NovaFullcalendar;

use Laravel\Nova\ResourceTool;

class NovaFullcalendar extends ResourceTool
{
    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */
    public function name()
    {
        return 'Календарь';
    }

    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'nova-fullcalendar';
    }
}
