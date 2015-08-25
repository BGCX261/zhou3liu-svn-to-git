<?php

/**
 * 轮播图片挂件
 *
 * @return  array   $image_list
 */
class Cycle_imageWidget extends BaseWidget
{
    var $_name = 'cycle_image';
    var $_num  = 5;

    function _get_data()
    {
        return $this->options;
    }

    function get_config_datasrc()
    {
        $this->assign('numbers', range(0, $this->_num - 1 - count($this->options)));
    }

    function parse_config($input)
    {
        $result = array();
        $images = $this->_upload_image();
        for ($i = 0; $i < $this->_num; $i++)
        {
            if (!empty($images[$i]))
            {
                $input['ad_image_url'][$i] = $images[$i];
            }

            if (!empty($input['ad_image_url'][$i]))
            {
                $result[] = array(
                    'ad_image_url' => $input['ad_image_url'][$i],
                    'ad_link_url'  => $input['ad_link_url'][$i]
                );
            }
        }

        return $result;
    }

    function _upload_image()
    {
        import('uploader.lib');

        $images = array();
        for ($i = 0; $i < $this->_num; $i++)
        {
            $file = array();
            foreach ($_FILES['ad_image_file'] as $key => $value)
            {
                $file[$key] = $value[$i];
            }

            if ($file['error'] == UPLOAD_ERR_OK)
            {
                $uploader = new Uploader();
                $uploader->allowed_type(IMAGE_FILE_TYPE);
                $uploader->addFile($file);
                $uploader->root_dir(ROOT_PATH);
                $images[$i] = $uploader->save('data/files/mall/template', $uploader->random_filename());
            }
        }

        return $images;
    }
}

?>