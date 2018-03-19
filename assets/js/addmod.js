$(function() {
    $('#addmform').on('submit', false).parsley()
    $('#addb').on('click', function(e){
        var data = {op : 'addmod', name: '', leader : ''}
        var frm = $('#addmform')
        
        $('input:text', frm).each(function(index, element){
            data[$(this).attr('name')] = $(this).val()
        })
        
        $($('#leadersel'), frm).each(function(index, element){
            data[$(this).attr('name')] = $(this).val()
        })
        
        $.post('{{base}}/ajax.php', data, function(ret){
            $('#addm').modal('hide')
            $('#mtab tbody').append('<tr data-id="'+ret+'"><td>'+data.name+'</td>'+
                '<td>'+data.leader+'</td>'+
                '<td><i class="editb fa fa-pencil"></i></td>' +
                '<td><i class="delb fa fa-trash-o"></i></td></tr>'
            )
            doBGFade('#mtab tbody tr:last-child', [245,255,159], [255,255,255], 'transparent', 75, 20, 4)
        })
    })
    $('#mtab').on('click', function(e){
        var x = $(e.target)
        if (x.hasClass('delb'))
        {
            e.preventDefault();
            e.stopPropagation();
            bootbox.confirm('Are you sure you you want to delete this module?', function(r){
                if (r)
                { // user picked OK
                    var tr = x.parent().parent()
                    $.post('{{base}}/ajax.php', {
                        op:	'delbean',
                        bean: 'module',
                        id: tr.data('id')
                        },
                        function(data){
                            tr.css('background-color', 'yellow').fadeOut(1500, function(){ tr.remove() })
                        }
                    )
                }
            })
        }
        else if (x.hasClass('editb'))
        {
            var tr = x.parent().parent() // change to edit module
            window.location.href = '{{base}}/configure/edit/module/' + tr.data('id');
        }
        else if (x.hasClass('active'))
        {
            dotoggle(e, x, 'user', 'active')
        }
    })
})