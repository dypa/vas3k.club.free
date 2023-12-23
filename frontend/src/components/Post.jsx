import { createSignal, onMount } from 'solid-js'
import { getApiHost, reloadPage } from '../App'

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

  reloadPage()
}

export const Post = (props) => {
  const post = props.post

  const [uri, setUri] = createSignal('')
  const [title, setTitle] = createSignal(' 🎁🎁🎁 ' + new Date(post.lastModified.date).toLocaleDateString('ru-RU') + ' 🎁🎁🎁 ')
  const [isNew, setIsNew] = createSignal(true)

  onMount(() => {
    setUri(getApiHost() + '/go/' + post.id)

    if (post.title?.length > 0) {
      setIsNew(false)
      setTitle(post.title?.replace(/^→ /, ''))
    }
  })

  return (
    <>
      {!isNew() && !post.like && <span><a title='👍' onClick={() => { vote(1, parseInt(post.id)) }} style={{ cursor: 'pointer' }}>🔥</a>&nbsp;</span>}
      {!isNew() && post.like && <span><a title='👎' onClick={() => { vote(2, parseInt(post.id)) }} style={{ cursor: 'pointer' }}>🌚</a>&nbsp;</span>}
      &nbsp;&nbsp;&nbsp;
      <a
        class="go"
        target="_blank"
        href={uri()}
        onClick={() => { reloadPage() }}
      >{title()}</a>
    </>
  )
}
