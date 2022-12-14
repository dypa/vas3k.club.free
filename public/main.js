function load(type, word) {
    const postsElement = $('#posts');
    postsElement.empty()
    noPostsFound(postsElement);

    $.get('/api/' + type + ('search' == type && (typeof word !== 'undefined' || word != '') ? '/' + word.replaceAll('?', '') : ''), function (data) {
        let currentDate;

        if (0 != data.length) {
            $('.center', postsElement).remove()
        }

        data.forEach(function (element) {
            const date = (element.createdAt ? element.createdAt.date : element.updatedAt.date).substring(0, 10);
            if (currentDate != date) {
                currentDate = date
                let link = ''
                if ('new' == type) {
                    link = ' <a class="none" href="/date/' + currentDate + '">📥</a>'
                }
                postsElement.append('<div class="next"><h3>' + currentDate + '' + link + '</h3><ul></ul></div>')
            }
            let isNew = !element.title ? true : false

            let votes = typeof element.votes !== "undefined" && element.votes !== null ? element.votes : 0

            $('.next:last ul', postsElement).append('<li>' +
                '<a title="' + votes + '" class="go ' + (isNew ? 'new' : '') + '" target="_blank" href="/go/' + element.id + '">' +
                (isNew ? element.type + ' ' + element.clubId : element.type + ' ➡ ' + element.title) +
                '</a></li>')

            if ($('.next:last a.new', postsElement).length > 0) {
                $('.next:last h3 a.none', postsElement).removeClass('none')
            }

            if ('done' == type || 'search' == type) {
                $('.next:last ul li:last', postsElement).prepend('<a href="#" style="color: green;text-decoration: none" onclick="vote(1, ' + element.id + ')">▲</a> - ')
            }
            if ('favorite' == type) {
                $('.next:last ul li:last', postsElement).prepend('<a href="#" style="color: red;text-decoration: none" onclick="vote(2, ' + element.id + ')">▼</a> - ')
            }
        })

        if ('new' == type) {
            $('a.go', postsElement).on('click', function () {
                const ul = $(this).closest('ul')
                const ulLen = ul.children().length

                $(this).parent().remove()

                const text = $('#progress-text').text()
                const progress = text.split('/')
                $('#progress-text').text((parseInt(progress[0]) + 1) + '/' + progress[1])

                if (1 == ulLen) {
                    ul.parent().remove()                    
                }

                if (postsElement.children().length == 0) {
                    noPostsFound(postsElement)
                }
            })
        }
    })
}

function noPostsFound(posts) {
    posts.html('<div class="center" "><pre>¯\\_(ツ)_/¯</pre></div>');
}

function vote(direction, id) {
    $.get('/api/vote/' + direction + '/' + id, function () {
        load('favorite');
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