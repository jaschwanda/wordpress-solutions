tinymce.init(
   {
      menubar : false,
      paste_data_images : true,
      plugins : [''
      + ' autoresize'
      + ' charmap'
      + ' code'
      + ' lists'
      + ' paste'
      + ' table'
      + ' help'
      ],
      toolbar1 : ''
      + ' undo redo |'
      + ' paste pastetext |'
      + ' formatselect |'
      + ' bold italic underline |'
      + ' alignleft aligncenter alignright alignjustify |'
      + ' bullist numlist outdent indent |'
      + ' table tabledelete | tableprops tablerowprops tablecellprops | tableinsertrowbefore tableinsertrowafter tabledeleterow | tableinsertcolbefore tableinsertcolafter tabledeletecol |'
      + ' charmap |'
      + ' code help '
      ,
      selector : '.usi-wordpress-tiny',
      verify_html : true
   }
);
