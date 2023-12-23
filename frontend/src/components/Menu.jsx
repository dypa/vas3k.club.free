import { createSignal, onMount } from 'solid-js'
import { getApiHost, getProgress } from '../App'

export const Menu = () => {
  const [viewed, setVewed] = createSignal(0)
  const [total, setTotal] = createSignal(0)
  const [updated, setUpdated] = createSignal(0)
  const [liked, setLiked] = createSignal(0)

  onMount(async () => {
    updateProgress()
  })

  async function updateProgress() {
    const json = await getProgress()
    setVewed(json.viewed)
    setTotal(json.total)
    setUpdated(json.updated)
    setLiked(json.liked)
  }

  async function markAllAsRead() {
    const response = await fetch(getApiHost() + '/api/mark-all-as-read')

    if (!response.ok) {
      return
    }

    updateProgress()
  }

  return (
    <menu>
      Open club reader <sup title={(total() - viewed()).toString()}>{viewed()}/{total()}</sup>
      &nbsp;|&nbsp;
      <a href="/">new</a>
      &nbsp;|&nbsp;
      <a href="/updated">updated</a> <sup class="sup" onClick={() => { markAllAsRead() }}>{updated()}</sup>
      &nbsp;|&nbsp;
      <a href="/favorite">favorite</a> <sup>{liked()}</sup>
      &nbsp;|&nbsp;
      <a href="/done">done</a>
      &nbsp;|&nbsp;
      <a href="/search">search</a>
      &nbsp;|&nbsp;
      <a href="/scrape">refresh</a>
    </menu>
  )
}
