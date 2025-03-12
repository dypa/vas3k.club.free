import { useParams, useNavigate } from '@solidjs/router'
import { For, Show, createSignal, createResource, Suspense } from 'solid-js'
import { NotFound } from './NotFound'
import { Post } from './Post'
import { getApiHost } from '../App'
import { Loading } from './Loading'

function generateUri(type) {
  return getApiHost() + '/api/filter/' + type
}

export const Posts = (props) => {
  const navigate = useNavigate();

  const [type, setType] = createSignal(props.type)

  const params = useParams();
  const [page, setPage] = createSignal(0)
  const [total, setTotal] = createSignal(0)

  if (params.page !== undefined) {
    setPage(parseInt(params.page))
  }

  const [posts] = createResource(page, loadPosts, {initialValue: []})

  async function loadPosts(page) {
    const response = await fetch(generateUri(type()) + '/' + page)
    if (!response.ok) {
      return
    }

    const json = await response.json()
    setTotal(json.total)

    return json.data
  }

  function nav(page) {
    setPage(page)
    navigate('/' + type() + '/' + page, { replace: true })
  }

  return (
    <Suspense fallback={<Loading />}>

      <Show when={total() == 0}><NotFound /></Show>

      <Show when={posts().length > 0}>
        <ul>
          <For each={posts()} >
            {(post) => <li><Post post={post} /></li>}
          </For>
        </ul>
      </Show>

      <Show when={total() !=1 && total() > page()}>
        <div class="is-center">
          <a onClick={() => { nav((page() > 0 ? page() - 1 : page())) }} >◄◄◄</a>
          <span>&nbsp; {page() + 1} / {total()} &nbsp;</span>
          <a onClick={() => { nav((page() + 1 < total() ? page() + 1 : page())) }} >►►►</a>
        </div>
      </Show>
    </Suspense>
  )
}
