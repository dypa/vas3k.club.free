import { For, createSignal, onMount } from 'solid-js'
import { useSearchParams } from '@solidjs/router'
import { getApiHost } from '../App'
import { Post } from '../components/Post'
import { NotFound } from '../components/NotFound'

export const Search = () => {
  const [searchParams, setSearchParams] = useSearchParams()
  const [searchQuery, setSearchQuery] = createSignal('')
  const [results, setResults] = createSignal([])

  // TODO async so results can be wrong
  async function loadResults(term) {
    setResults([])

    term = term.trim()
    if (!term) {
      return
    }

    setSearchParams({ q: term })

    setSearchQuery(term)

    let formData = new FormData();
    formData.append('word', searchQuery());

    const response = await fetch(getApiHost() + '/api/search', {
      method: 'POST',
      body: formData
    })

    if (!response.ok) {
      return
    }

    const json = await response.json()
    setResults(json)

    window.scrollTo(0, 0)
  }

  onMount(async () => {
    const term = searchParams.q
    if (term) {
      loadResults(term)
    }
  })

  return (
    <>
      <input
        autoFocus="1"
        id="search"
        type="text"
        placeholder="Что ищем?!"
        value={searchQuery()}
        onInput={async (event) => { await loadResults(event.currentTarget.value) }}
      />

      <Show when={results().length == 0}><NotFound /></Show>

      <ul>
        <For each={results()}>
          {(post) => <li><Post post={post} /></li>}
        </For>
      </ul>
    </>
  )
}
