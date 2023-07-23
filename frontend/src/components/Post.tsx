import { Component, createSignal, onMount } from 'solid-js'
import { getApiHost, reloadPage } from '../App'

export type PostType = {
    id: string
    createdAt?: object
    lastModified: object
    deletedAt?: object
    viewedAt?: object
    postType: string
    title?: string
    like: boolean
    type: string
}

type Props = {
    post: PostType
}

async function vote(direction: number, id: number) {
    const uri = getApiHost() + '/api/vote/' + direction + '/' + id
    const response = await fetch(uri)

    if (!response.ok) {
        return
    }

    const json = await response.json()
    if (json != true) {
        return
    }

    reloadPage()
}

export const Post: Component<Props> = (props: Props) => {
    const post: PostType = props.post

    const [uri, setUri] = createSignal('')
    const [title, setTitle] = createSignal('')

    onMount(() => {
        setUri(getApiHost() + '/go/' + post.id)

        if (post.title?.length > 0) {
            setTitle(post.title)
        } else {
            // setTitle(post.postType + ' ' + post.id)
            setTitle(new Date(post.lastModified.date).toLocaleDateString('ru-RU') + ' 🎁 ' + post.id + ' 😲 ')
        }
    })

    return (
        <>
            {!post.like && <span><a title='👍' onClick={() => { vote(1, parseInt(post.id)) }}>🔥</a>&nbsp;</span>}
            {post.like && <span><a title='👎' onClick={() => { vote(2, parseInt(post.id)) }}>🌚</a>&nbsp;</span>}
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