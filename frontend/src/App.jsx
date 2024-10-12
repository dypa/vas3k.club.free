import { Route, Router, Navigate } from '@solidjs/router'

import { Menu } from './components/Menu'
import { NotFound } from './components/NotFound'

import { Done, Favorite, New, Updated, Deleted } from './routes/Filters'
import { Scrape } from './routes/Scrape'
import { Search } from './routes/Search'

export function getApiHost() {
  return '//localhost:' + import.meta.env.VITE_API_PORT
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
        <div class="col_1">&nbsp;</div>
        <div class="col_10" id="content">
          <Router>
            <Route path="/" component={() => <Navigate href="/new" />} />;

            <Route path="/new" component={New} />
            <Route path="/new/:page" component={New} />

            <Route path="/updated" component={Updated} />
            <Route path="/updated/:page" component={Updated} />

            <Route path="/favorite" component={Favorite} />
            <Route path="/favorite/:page" component={Favorite} />

            <Route path="/done" component={Done} />
            <Route path="/done/:page" component={Done} />

            <Route path="/deleted" component={Deleted} />
            <Route path="/deleted/:page" component={Deleted} />

            <Route path="/search" component={Search} />
            <Route path="/search/:page" component={Search} />

            <Route path="/scrape" component={Scrape} />

            <Route path="*" component={NotFound} />
          </Router>
        </div>
        <div class="col_1">&nbsp;</div>
      </div>

      <div class="row">
        <div class="col_12">&nbsp;</div>
      </div>

    </div>
  )
}

export default App
