import { Route, Router } from '@solidjs/router'
import type { Component } from 'solid-js'

import { Menu } from './components/Menu'
import { NotFound } from './components/NotFound'

import { Done, Favorite, New, Updated } from './routes/Filters'
import { Scrape } from './routes/Scrape'
import { Search } from './routes/Search'

export function getApiHost (): string {
  return '//localhost:' + import.meta.env.VITE_API_PORT
}

export function reloadPage (): void {
  // TODO this is HACK

  setTimeout(() => { location.reload() }, 1000)
}

// routing not works with /:slug, see solidjs/solid-router/issues/264

const App: Component = () => {
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
            <Route path="/new" component={New} />
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
