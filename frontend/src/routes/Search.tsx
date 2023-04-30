import { Component, For, createSignal } from "solid-js"
import { getApiHost } from "../App"
import { Post } from "../components/Post"
import { NotFound } from "../components/NotFound"

const [searchTerm, setSearchTerm] = createSignal('')
const [results, setResults] = createSignal([])

//TODO async so results can be wrong
async function loadResults(term: string) {
    setResults([])

    setSearchTerm(term)
    const response = await fetch(getApiHost() + '/api/search/' + searchTerm())

    if (!response.ok) {
        return
    }

    const json = await response.json()
    setResults(json)

    window.scrollTo(0, 0)
}

export const Search: Component = () => {
    return (
        <>
            <input
                id="search"
                type="text"
                placeholder="Что ищем?!"
                value={searchTerm()}
                onInput={(event) => loadResults(event.currentTarget.value)}
            />
            <ul>
                <For each={results()} fallback={<NotFound />}>
                    {(post) => <li><Post post={post} /></li>}
                </For>
            </ul>
        </>
    )
}