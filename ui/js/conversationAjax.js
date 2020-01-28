    $(document).ready(function() {
        var number_comments = 1;
        var offset = 5;
        var html = "";
        var urlChunks = window.location.pathname.split('/');
        var convId = urlChunks[urlChunks.length - 1];

        $("#load").click(function() {
            $.ajax({
                url: '../../loadMessage.php',
                type: 'POST',
                data: {'number_comments': number_comments, 'offset': offset, 'convId': convId},
                dataType: 'json',
                success : function(data) {
                    for( var msg of data) {
                        html +='<p class="' + msg.send_user_id + '"id="'+ msg.message_id +'">'+msg.message+'</p>';
                    }
                    $('#comments').html(html);
                }
            });
            offset = offset + 1;
        });

        $('#sendMessage').submit(function(event) {
            event.preventDefault();
            var url = $(this).attr('action');
            var postdata = $(this).serialize();
            if (postdata !== "message=")
            {
               $.post(
                url,
                postdata
                ); 
                $('#message').val('');
            }
        });

        $.ajax({
            url: '../../loadMessage.php',
            type: 'POST',
            data: {'number_comments': 5, 'offset': 0, 'convId': convId},
            dataType: 'json',
            success : function(data) {
                for( var msg of data) {
                    html +='<p class="' + msg.send_user_id + '"id="' + msg.message_id +'">'+msg.message+'</p>';
                }
                $('#comments').html(html);
            }
        });

        setInterval(function() {
            var id_first = $("p").first().attr('id'); 
            if(id_first) {
                $.ajax({
                    url: '../../loadMessage.php',
                    type: 'POST',
                    data: {'number_comments': 1, 'offset': 0, 'convId': convId, 'id_first': id_first},
                    dataType: 'json',
                    success : function(data) {
                        for( var msg of data) {
                            html = html.replace (/^/, '<p class="' + msg.send_user_id + '"id="'+ msg.message_id +'">'+msg.message+'</p>');
                        }
                        $('#comments').html(html);
                    }});
                    id_first = $("p").first().attr('id'); 

                }
        }, 2000);

    });        

