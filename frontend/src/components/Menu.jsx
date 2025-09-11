import { createSignal, onMount } from 'solid-js'
import { getApiHost } from '../App'

export const Menu = () => {
  const [viewed, setVewed] = createSignal(0)
  const [total, setTotal] = createSignal(0)
  const [updated, setUpdated] = createSignal(0)
  const [liked, setLiked] = createSignal(0)
  const [newCnt, setNewCnt] = createSignal(0)

  onMount(async () => {
    updateProgress()
  })

  setInterval(() => {
    updateProgress()
  }, 1000 * 60 * 10)


  async function updateProgress() {
    const response = await fetch(getApiHost() + '/api/progress')

    if (!response.ok) {
      return
    }

    const json = await response.json()
    setVewed(json.viewed)
    setTotal(json.total)
    setUpdated(json.updated)
    setLiked(json.liked)
    setNewCnt(json.new)
  }

  async function markAllAsRead() {
    const response = await fetch(getApiHost() + '/api/mark-all-as-read')

    if (!response.ok) {
      return
    }

    updateProgress()
  }

  return (
    <div class="row">
      <div class="col is-center">
        <menu>
          <span title={total().toString() + "-" + viewed().toString() + "=" + (total() - viewed()).toString()}>Open club reader</span>
          &nbsp;|&nbsp;
          <a href="/new">new</a><Show when={newCnt() > 0}><sup>{newCnt()}</sup></Show>
          &nbsp;|&nbsp;
          <a href="/updated">updated</a> <sup class="sup" onClick={() => { markAllAsRead() }}>{updated()}</sup>
          &nbsp;|&nbsp;
          <a href="/favorite">favorite</a> <sup>{liked()}</sup>
          &nbsp;|&nbsp;
          <a href="/deleted">deleted</a>
          &nbsp;|&nbsp;
          <a href="/done">done</a>
          &nbsp;|&nbsp;
          <a href="/search">search</a>
          &nbsp;|&nbsp;
          <a href="/scrape">refresh</a>
        </menu>
      </div>
    </div>
  )
}
