<?php

array(
  'pageId' or 'id'    => 1,
  'category'          => 'Example Category',
  'categoryId'        => 3,
  'subCategory'       => 'Another Category',
  'subCategoryId'     => 4,
  'pageDate'          => '2000-12-31', // depending on the date format settings from the backend
  'pageDateTimestamp' => 1325393999,
  'title'             => 'Title Example',
  'thumbnail'         => '<img src="/path/thumb_page1.png" class="feinduraThumbnail" alt="Thumbnail" title="Title Example">',
  'thumbnailPath'     => '/path/thumb_page1.png',
  'content'           => "\n".'<p>Content Text</p>'."\n",
  'description'       => 'Short description of the page',
  'tags'              => 'tag1,tag2,tag3',
  'href'              => '?category=3&page=1', // or a speaking url, if activated
  'plugins'           => array(
          'imageGallery' => array(
              'active'          => true,
              'galleryPath'     => '/upload/gallery/',
              'imageWidth'      => 800,
              'imageHeight'     => null,
              'thumbnailWidth'  => 160,
              'thumbnailHeight' => null,
              'tag'             => 'table',
              'breakAfter'      => 3
          ),
          'pageRating' => array(
              'active' => false,
              'value'  => 0,
              'votes'  => 0
          )
      ),
  'error'             => false // will be set to TRUE when the page doesn't exist or is deactivated
  )

?>