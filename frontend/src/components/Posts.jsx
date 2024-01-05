import { useParams, A } from '@solidjs/router'
import { For, Show, createSignal, onMount, createResource, Suspense } from 'solid-js'
import { NotFound } from './NotFound'
import { Post } from './Post'
import { getApiHost } from '../App'

function generateUri(type) {
  return getApiHost() + '/api/filter/' + type
}

export const Posts = (props) => {
  const [type, setType] = createSignal(props.type)

  const params = useParams();
  const [page, setPage] = createSignal(0)
  const [total, setTotal] = createSignal(0)

  if (params.page !== undefined) {
    setPage(parseInt(params.page))
  }

  const [posts] = createResource(page(), loadPosts)

  async function loadPosts(page) {
    const response = await fetch(generateUri(type()) + '/' + page)
    if (!response.ok) {
      return
    }

    const json = await response.json()
    setTotal(json.total)

    return json.data
  }

  return (
    <Suspense fallback={<NotFound />}>
      <ul>
        <For each={posts()} fallback={<NotFound />}>
          {(post) => <li><Post post={post} /></li>}
        </For>
      </ul>

      <Show when={total() > page()}>
        <div class="center">
          <A href={'/' + type() + '/' + (page() > 0 ? page() - 1 : page())}>◄◄◄</A>
          <span> {page() + 1} / {total()} </span>
          <A href={'/' + type() + '/' +  (page() + 1 <= total() ? page() + 1 : page())}>►►►</A>
        </div>
      </Show>
    </Suspense>
  )
}
