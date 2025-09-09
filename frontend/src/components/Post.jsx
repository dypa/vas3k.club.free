import { createSignal, onMount } from 'solid-js'
import { getApiHost } from '../App'

export const Post = (props) => {
  const post = props.post

  const [title, setTitle] = createSignal(' 🎁🎁🎁 ' + new Date(post.lastModified.date).toLocaleDateString('ru-RU') + ' 🎁🎁🎁 ')
  const [isNew, setIsNew] = createSignal(true)

  onMount(() => {
    if (post.title?.length > 0) {
      setIsNew(false)
      setTitle(post.title?.replace(/^→ /, ''))
    }
  })

  const url = "https://vas3k.club/" + post.postType + "/" + post.id + "/";

  async function vote(direction, id) {
  const uri = getApiHost() + '/api/vote/' + direction + '/' + id
  const response = await fetch(uri)

  if (!response.ok) {
    return
  }

  const json = await response.json()
  if (json !== true) {
    return
  }

  props.onOpenPost()
}

  return (
    <>
      {!isNew() && !post.like && <span><a title='👍' onClick={() => { vote(1, parseInt(post.id)) }} style={{ cursor: 'pointer' }}>🔥</a>&nbsp;</span>}
      {!isNew() && post.like && <span><a title='👎' onClick={() => { vote(2, parseInt(post.id)) }} style={{ cursor: 'pointer' }}>🌚</a>&nbsp;</span>}
      {!isNew() &&
        <span>
          &nbsp;&nbsp;&nbsp;
          <a
            class="cache"
            target="_blank"
            href={url}
          >🌐</a>
        </span>}

      &nbsp;&nbsp;&nbsp;
      <a
        class="go"
        target="_blank"
        href={getApiHost() + '/go/' + post.id}
        onClick={() => props.onOpenPost(post.id)}
      >{title()}</a>
    </>
  )
}
