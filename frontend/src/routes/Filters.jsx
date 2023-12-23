import { getApiHost } from '../App'
import { Posts } from '../components/Posts'

function generateUri(type) {
  return getApiHost() + '/api/filter/' + type
}

export const New = () => {
  return (
    <Posts uri={generateUri('new')} />
  )
}

export const Done = () => {
  return (
    <Posts uri={generateUri('done')} />
  )
}

export const Updated = () => {
  return (
    <Posts uri={generateUri('updated')} />
  )
}

export const Favorite = () => {
  return (
    <Posts uri={generateUri('favorite')} />
  )
}
