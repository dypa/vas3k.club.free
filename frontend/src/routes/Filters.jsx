
import { Posts } from '../components/Posts'

export const New = () => {
  return (
    <Posts type='new' />
  )
}

export const Done = () => {
  return (
    <Posts type='done' />
  )
}

export const Updated = () => {
  return (
    <Posts type='updated' />
  )
}

export const Favorite = () => {
  return (
    <Posts type='favorite' />
  )
}

export const Deleted = () => {
  return (
    <Posts type='deleted' />
  )
}