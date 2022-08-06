function load(type, word) {
    const posts = $('#posts');
    posts.empty()
    posts.html('<div class="center" "><pre>¯\\_(ツ)_/¯</pre></div>')

    $.get('/api/' + type + ('search' == type && (typeof word !== 'undefined' || word != '') ? '/' + word.replaceAll('?', '') : ''), function (data) {
        let currentDate;

        if (0 != data.length) {
            $('#posts .center').remove()
        }

        data.forEach(function (element) {
            const date = (element.createdAt ? element.createdAt.date : element.updatedAt.date).substring(0, 10);
            if (currentDate != date) {
                currentDate = date
                let link = ''
                if ('new' == type) {
                    link = ' <a class="none" href="/date/' + currentDate + '">📥</a>'
                }
                $('#posts').append('<div class="next"><h3>' + currentDate + '' + link + '</h3><ul></ul></div>')
            }
            let isNew = !element.title ? true : false

            $('#posts .next:last ul').append('<li><a class="go ' + (isNew ? 'new' : '') + '" target="_blank" href="/go/' + element.id + '">' + (isNew ? element.type + ' ' + element.clubId : element.type + ' ➡ ' + element.title) + '</a></li>')

            if ($('#posts .next:last a.new').length > 0) {
                $('#posts .next:last h3 a.none').removeClass('none')
            }

            if ('past' == type || 'search' == type) {
                $('#posts .next:last ul li:last').prepend('<a href="#" style="color: green" onclick="vote(1, ' + element.id + ')">▲</a> - ')
            }
            if ('best' == type) {
                $('#posts .next:last ul li:last').prepend('<a href="#" style="color: red" onclick="vote(2, ' + element.id + ')">▼</a> - ')
            }
        })

        if ('new' == type) {
            $('#posts a.go').on('click', function () {
                const ul = $(this).closest('ul')
                const ulLen = ul.children().length

                $(this).parent().remove()

                const text = $('#progress-text').text()
                const progress = text.split('/')
                $('#progress-text').text((parseInt(progress[0]) + 1) + '/' + progress[1])

                if (1 == ulLen) {
                    ul.parent().remove()
                }
            })
        }
    })
}

function vote(direction, id) {
    $.get('/api/vote/' + direction + '/' + id, function () {
        load('best');
    })
}

$(document).ready(function () {
    $.get('/api/progress', function (data) {
        $('#progress-text').text(data.viewed + '/' + data.total)
        $('#progress-text').attr('title', (data.total - data.viewed))
    })
    load('new');
})

$(document).ready(function () {
    $('#find').on("click", function () {
        $('input', this).removeClass('none')
        $('a', this).addClass('none')
    })
    $('#find input').on("keyup", function (e) {
        if (e.keyCode == 13) {
            load('search', $(this).val());
        }
    })
    $('#find input').on("blur", function () {
        let parent = $(this).parent()
        $('input', parent).addClass('none')
        $('a', parent).removeClass('none')
    })
})