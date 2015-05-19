/**
Plugin Name: Selected Colorbox Image
Plugin URI: http://www.vipulhadiya.com/selected-colorbox-image/
Description:Select images just with a single click to show theme in colorbox popup.
Version: 1.0.0
Author: Vipul Hadiya
Author URI: http://vipulhadiya.com
Text Domain: allimg
License: GPL2
*/

jQuery(function($){
    var allimgs = $('#allimgtable').dataTable({
		"processing": true,
        "bFilter":false,
		"serverSide": true,
		"iDisplayLength":10,
		"ajax": {
			"url": ajaxurl + "?action=allimgs"
		},
		"columns": [
			{"data": "ID", "width": "30px"},
			{"data": "guid","width":"110px"},
			{"data": "post_title", "width": "250px", "bSortable": false},
            {"data": "post_parent","width": "150px", "bSortable": false},
            {"data": "","width": "50px", "bSortable": false}
		],
		"order": [[0, "asc"]],
		"pagingType": "full_numbers",
		"bLengthChange": false,
        "columnDefs": [
			{
				"render": function (data, type, row)
				{
					return '<label><input '+((parseInt(row.state))?'checked="checked"':'')+'type="checkbox" class="cb_allimg" data-id_attachment="'+row.ID+'" /></label>';
				},
				"targets": 4
			},
            {
				"render": function (data, type, row)
				{
					return '<img src="'+row.guid+'" height="100" width="100">';
				},
				"targets": 1
			}
        ]
	}).on('draw.dt', function ()
	{
	   $('#allimgtable').find('.cb_allimg').click(function(){
            $.ajax({
                type:'POST',
                url:ajaxurl + "?action=cb_allimg",
                data:{
                    id_attachment:$(this).data('id_attachment'),
                    allimg_state:($(this).is(':checked')?1:0)
                },
                success:function(data){

                },
                error:function(data){

                }
            });
        });
	});
});