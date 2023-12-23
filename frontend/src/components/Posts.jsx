import { useSearchParams } from '@solidjs/router'
import { For, Show, createSignal, onMount } from 'solid-js'
import { NotFound } from './NotFound'
import { Post } from './Post'

export const Posts = (props) => {
  const [posts, setPosts] = createSignal([])
  const [searchParams, setSearchParams] = useSearchParams()
  const [page, setPage] = createSignal(0)
  const [total, setTotal] = createSignal(0)

  onMount(async () => {
    if (searchParams.page) {
      const page = parseInt(searchParams.page)
      setPage(page)
    }

    loadPosts()
  })

  async function loadPosts() {
    setPosts([])

    const uri = props.uri

    if (!uri) {
      return
    }

    const response = await fetch(uri + '/' + page())

    if (!response.ok) {
      return
    }

    const json = await response.json()
    setPosts(json.data)
    setTotal(json.total)
  }

  return (
    <>
      <Show when={posts().length === 0}>
        <NotFound />
      </Show>

      <Show when={posts().length > 0}>
        <ul>
          <For each={posts()} fallback={<NotFound />}>
            {(post) => <li><Post post={post} /></li>}
          </For>
        </ul>

        <Show when={total() > page()}>
          <div class="center">
            <a href={'?page=' + (page() > 0 ? page() - 1 : page())}>◄◄◄</a>
            <span> {page() + 1} / {total()} </span>
            <a href={'?page=' + (page() + 1 < total() ? page() + 1 : page())}>►►►</a>
          </div>
        </Show>
      </Show>
    </>
  )
}
