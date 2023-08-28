import { A } from '@solidjs/router'
import { type Component, createSignal, onMount } from 'solid-js'
import { getApiHost, reloadPage } from '../App'

export const Menu: Component = () => {
  const [viewed, setVewed] = createSignal(0)
  const [total, setTotal] = createSignal(0)
  const [updated, setUpdated] = createSignal(0)
  const [liked, setLiked] = createSignal(0)

  onMount(async () => {
    const response = await fetch(getApiHost() + '/api/progress')

    if (!response.ok) {
      return
    }

    const json = await response.json()
    setVewed(json.viewed)
    setTotal(json.total)
    setUpdated(json.updated)
    setLiked(json.liked)
  })

  async function markAllAsRead (): Promise<void> {
    const response = await fetch(getApiHost() + '/api/mark-all-as-read')

    if (!response.ok) {
      return
    }

    reloadPage()
  }

  return (
        <menu>
            Open club reader <sup title={(total() - viewed()).toString()}>{viewed()}/{total()}</sup>
            &nbsp;|&nbsp;
            <A href="/">new</A>
            &nbsp;|&nbsp;
            <A href="/updated">updated</A> <sup class="sup" onClick={() => { markAllAsRead() }}>{updated()}</sup>
            &nbsp;|&nbsp;
            <A href="/favorite">favorite</A> <sup>{liked()}</sup>
            &nbsp;|&nbsp;
            <A href="/done">done</A>
            &nbsp;|&nbsp;
            <A href="/search">search</A>
            &nbsp;|&nbsp;
            <A href="/scrape">refresh</A>
        </menu>
  )
}
