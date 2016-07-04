<?php
/**
 * hbData
 * --------------------------------------------------------------------------------------------------
 * 版权所有 2016-
 * 网站地址:
 * --------------------------------------------------------------------------------------------------
 * Author: AlanJager
 * Release Date: 2016-7-4
 */

if (!defined('IN_HBDATA')) {
    die('Accident occured, please try again.');
}
/**
 * 网站地图
 * @name SiteMap
 * @version v1.0
 * @author AlanJager
 */

class SiteMap {
    var $header = "<\x3Fxml version=\"1.0\" encoding=\"UTF-8\"\x3F>\n\t<urlset xmlns=\"http://www.google.com/schemas/sitemap/0.84\">";
    var $footer = "\t</urlset>";
    var $output;

    /**
     * SiteMap constructor.
     * @param $domain
     * @param string $today
     * @return SiteMap
     */
    function SiteMap($domain, $today = '') {
        $this->domain = $domain;
        $this->today = $today;
    }

    /**
     * build sitemap
     * @return header and footer
     */
    function build_sitemap() {
        $output = $this->header . "\n\n";
        $output .= $this->read_item();
        $output .= $this->footer;
        return $output;
    }

    /**
     * ergodic category to sitemap format
     * @return string
     */
    function read_item() {
        $item = $this->array_item();

        $arr = "\t\t<url>\n";
        $arr .= "\t\t\t<loc>$this->domain</loc>\n";
        $arr .= "\t\t\t<lastmod>$this->today</lastmod>\n";
        $arr .= "\t\t\t<changefreq>hourly</changefreq>\n";
        $arr .= "\t\t\t<priority>0.9</priority>\n";
        $arr .= "\t\t</url>\n\n";

        foreach ($item as $row) {
            $arr .= "\t\t<url>\n";
            $arr .= "\t\t\t<loc>$row[url]</loc>\n";
            $arr .= "\t\t\t<lastmod>$row[date]</lastmod>\n";
            $arr .= "\t\t\t<changefreq>$row[changefreq]</changefreq>\n";
            $arr .= "\t\t\t<priority>0.9</priority>\n";
            $arr .= "\t\t</url>\n\n";
        }

        return $arr;
    }

    /**
     * get category data for the whole site
     * @return array
     */
    function array_item() {
        //single page list
        foreach ($GLOBALS['hbdata']->get_page_nolevel() as $row) {
            $item_array[] = array (
                "date" => $this->today,
                "changefreq" => 'weekly',
                "url" => $row['url']
            );
        }

        //category module
        foreach ($GLOBALS['_MODULE']['column'] as $module_id) {
            //category
            $item_array[] = array (
                "date" => $this->today,
                "changefreq" => 'hourly',
                "url" => $GLOBALS['hbdata']->rewrite_url($module_id . '_category')
            );
            foreach ($GLOBALS['hbdata']->get_category_nolevel($module_id . '_category') as $row) {
                $item_array[] = array (
                    "date" => $this->today,
                    "changefreq" => 'hourly',
                    "url" => $row['url']
                );
            }

            //content list
            foreach ($GLOBALS['hbdata']->get_list($module_id, 'ALL') as $row) {
                $item_array[] = array (
                    "date" => $row['add_time'],
                    "changefreq" => 'weekly',
                    "url" => $row['url']
                );
            }
        }

        //simple module
        foreach ($GLOBALS['_MODULE']['single'] as $module_id) {
            //not showing module
            $no_show = 'plugin';
            if (!in_array($module_id, explode('|', $no_show))) {
                $item_array[] = array (
                    "date" => $this->today,
                    "changefreq" => 'weekly',
                    "url" => $GLOBALS['hbdata']->rewrite_url($module_id)
                );
            }
        }
        return $item_array;
    }
}