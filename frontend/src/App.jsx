import { Route, Router } from '@solidjs/router'

import { Menu } from './components/Menu'
import { NotFound } from './components/NotFound'

import { Done, Favorite, New, Updated } from './routes/Filters'
import { Scrape } from './routes/Scrape'
import { Search } from './routes/Search'

export function getApiHost() {
  return '//localhost:' + import.meta.env.VITE_API_PORT
}

export async function getProgress() {
  const response = await fetch(getApiHost() + '/api/progress')

  if (!response.ok) {
    return
  }

  const json = await response.json()
  
  return json
}

export function reloadPage() {
  // TODO this is HACK

  setTimeout(() => { location.reload() }, 1000)
}

const App = () => {
  return (
    <div>
      <div class="row">
        <div class="col_12 menu">
          <Menu />
        </div>
      </div>

      <div class="row">
        <div class="col_12" id="content">
          <Router>
            <Route path="/" component={New} />
            <Route path="/updated" component={Updated} />
            <Route path="/favorite" component={Favorite} />
            <Route path="/done" component={Done} />

            <Route path="/search" component={Search} />

            <Route path="/scrape" component={Scrape} />

            <Route path="*" component={NotFound} />
          </Router>
        </div>
      </div>

      <div class="row">
        <div class="col_12">&nbsp;</div>
      </div>

    </div>
  )
}

export default App
