<?php
namespace my_paginate;

use think\paginator\driver\Bootstrap;

class AdminPaginate extends Bootstrap
{
    public function render()
    {
        if ($this->hasPages()) {
            return sprintf(
                '<div class="dataTables_wrapper"><div class="dataTables_paginate paging_full_numbers">%s %s</div></div>',
                $this->getPreviousButton('上一页'),
                '<span>'.$this->getLinks().'</span>',
                $this->getNextButton('下一页')
            );

        }
    }


    /**
     * 生成一个可点击的按钮
     *
     * @param  string $url
     * @param  int    $page
     * @return string
     */
    protected function getAvailablePageWrapper($url, $page)
    {
        return '<a class="paginate_button " href="' . htmlentities($url) . '">' . $page . '</a>';
    }

    /**
     * 生成一个禁用的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<a class="paginate_button "><span>' . $text . '</span></a>';
    }

    /**
     * 生成一个激活的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<a class="paginate_button current">' . $text . '</a>';
    }
}