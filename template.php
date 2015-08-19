<div class="wrap">
<h1>Serializer</h1>
<div class="card">
    <h3>Find</h3>
    <label>Find</label>
    <input type="text" placeholder="Find" id="the-serializer-find" />
    <label>Replace with</label>
    <input type="text" placeholder="Replace with" id="the-serializer-replaceWith" />

    <div id="the-serializer-select-table">
        <h3>Select database table</h3>
        <select id="the-serializer-table">
            <option value="NOTABLESELECTED">Select database table</option>
        <?php foreach( $this->get_table_list() as $table ) : ?>
            <option value="<?php echo $table; ?>"><?php echo $table; ?></option>
        <?php endforeach; ?>
        </select>
    </div>

    <div id="the-serializer-select-table-column">
        <h3>Select table column</h3>
        <select id="the-serializer-column"></select>
    </div>
<br />
    <button id="the-serializer-run" class="button">RUN</button>
    <style>
        #the-serializer-run,
        #the-serializer-select-table,
        #the-serializer-select-table-column{
            display: none;
        }
    </style>
    <script type="text/javascript">
		jQuery( document ).ready( function( $ ) {
            var SerializerInputFind = $('#the-serializer-find'),
                SerializerInputReplace = $('#the-serializer-replaceWith'),
                SerializerSelectTable = $('#the-serializer-select-table'),
                SerializerSelectTableColumn = $('#the-serializer-select-table-column'),
                SerializerButtonRun = $('#the-serializer-run');
            $('#the-serializer-find,#the-serializer-replaceWith').on('change', function(){
                if( $(this).val() == ''){
                    SerializerSelectTable.fadeOut();
                    SerializerSelectTableColumn.fadeOut();
                    SerializerButtonRun.fadeOut();
                } else if( !SerializerSelectTable.is(':visible') ){
                    SerializerSelectTable.fadeIn().val(SerializerSelectTable.find('option:first').val())
                }
            });
            SerializerSelectTable.find('select').on('change',function(){
                if( $(this).val() === 'NOTABLESELECTED' ){
                    if( SerializerSelectTableColumn.is(':visible') ){
                        SerializerSelectTableColumn.fadeOut();
                        SerializerButtonRun.fadeOut();
                    }
                    return;
                }

            	jQuery.post(ajaxurl, {
            		'action': 'the_serializer_columm_list',
            		'table': $(this).val()
            	}, function(response) {
                    SerializerSelectTableColumn.find('select').html('').append('<option value="NOCOLUMNSELECTED">Select a column</option>');
                    $.each(JSON.parse(response),function(index,column){
                        SerializerSelectTableColumn.find('select').append('<option value="'+column+'">' + column + '</option>');
                    });
                    if( !SerializerSelectTableColumn.is(':visible') )
                        SerializerSelectTableColumn.fadeIn().val(SerializerSelectTableColumn.find('option:first').val())
            	});

            });

            SerializerSelectTableColumn.find('select').on('change',function(){
                if( $(this).val() === 'NOCOLUMNSELECTED' ){
                    if( SerializerButtonRun.is(':visible') )
                        SerializerButtonRun.fadeOut();
                    return;
                } else {
                    if( !SerializerButtonRun.is(':visible') )
                        SerializerButtonRun.fadeIn();
                }
            });
            SerializerButtonRun.on('click',function(){
                // console.log( SerializerInputFind.val(), SerializerInputReplace.val(), SerializerSelectTable.find('select').val(), SerializerSelectTableColumn.find('select').val() )
                jQuery.post(ajaxurl, {
            		'action': 'the_serializer_run',
            		'find': SerializerInputFind.val(),
                    'replace': SerializerInputReplace.val(),
                    'table': SerializerSelectTable.find('select').val(),
                    'column': SerializerSelectTableColumn.find('select').val()
            	}, function(response) {
                    console.log(response);
            	});
            });
		});
	</script>
</div>
</div>
