import type { Component } from 'solid-js'

import { getApiHost } from '../App'
import { Posts } from '../components/Posts'

function generateUri(type: string): string {
    return getApiHost() + '/api/filter/' + type
}

export const New: Component = () => {
    return (
        <Posts uri={generateUri('new')} />
    )
}

export const Best: Component = () => {
    return (
        <Posts uri={generateUri('best')} />
    )
}

export const Done: Component = () => {
    return (
        <Posts uri={generateUri('done')} />
    )
}

export const Updated: Component = () => {
    return (
        <Posts uri={generateUri('updated')} />
    )
}

export const Favorite: Component = () => {
    return (
        <Posts uri={generateUri('favorite')} />
    )
}