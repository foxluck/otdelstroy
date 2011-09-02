<?php

class WidgetdsRTE extends dsRTE
{
    public function __construct($id)
    {
        parent::__construct($id, '<| font | fontsize || bold | italic | underline | strikeout || color | bgcolor || left | center | right || ul | ol || indent | outdent || link | rule || source |>', false);
    }
}

?>