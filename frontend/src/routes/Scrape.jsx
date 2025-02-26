import { Navigate } from '@solidjs/router'
import { Show, createSignal, onMount } from 'solid-js'
import { getApiHost } from '../App'

export const Scrape = () => {
  const randomEmoji = function () {
    const emoji = Array.from('👓🕶️🥽🥼🦺👔👕👖🧣🧤🧥🧦👗👘🥻🩱🩲🩳👙👚🪭👛👜👝🛍️🎒🩴👞👟🥾🥿👠👡🩰👢🪮👑👒🎩🎓🧢🪖⛑️📿💄💍💎🔇🔈🔉🔊📢📣📯🔔🔕🎼🎵🎶🎙️🎚️🎛️🎤🎧📻🎷🪗🎸🎹🎺🎻🪕🥁🪘🪇🪈📱📲☎️📞📟📠🔋🪫🔌💻🖥️🖨️⌨️🖱️🖲️💽💾💿📀🧮🎥🎞️📽️🎬📺📷📸📹📼🔍🔎🕯️💡🔦🏮🪔📔📕📖📗📘📙📚📓📒📃📜📄📰🗞️📑🔖🏷️💰🪙💴💵💶💷💸💳🧾💹✉️📧📨📩📤📥📦📫📪📬📭📮🗳️✏️✒️🖋️🖊️🖌️🖍️📝💼📁📂🗂️📅📆🗒️🗓️📇📈📉📊📋📌📍📎🖇️📏📐✂️🗃️🗄️🗑️🔒🔓🔏🔐🔑🗝️🔨🪓⛏️⚒️🛠️🗡️⚔️💣🪃🏹🛡️🪚🔧🪛🔩⚙️🗜️⚖️🦯🔗⛓️‍💥⛓️🪝🧰🧲🪜⚗️🧪🧫🧬🔬🔭📡💉🩸💊🩹🩼🩺🩻🚪🛗🪞🪟🛏️🛋️🪑🚽🪠🚿🛁🪤🪒🧴🧷🧹🧺🧻🪣🧼🫧🪥🧽🧯🛒🚬⚰️🪦⚱️🧿🪬🗿🪧🪪')

    const result = emoji[Math.floor(Math.random() * (emoji.length + 1))]

    return result !== undefined ? result : ''
  }

  const rnd = function () {
    return randomEmoji() + randomEmoji() + randomEmoji();
  }

  const [isDone, setDone] = createSignal(false)
  const [animate, setAnimate] = createSignal(rnd())

  const timerId = setInterval(() => setAnimate(rnd()), 500)
  setTimeout(() => { clearInterval(timerId) }, 10000)

  onMount(async () => {
    const response = await fetch(getApiHost() + '/api/scrape')
    if (!response.ok) {
      return
    }

    const json = await response.json()
    if (json !== true) {
      return
    }

    setDone(true)
  })

  return (
    <>
      <Show when={isDone()}>
        <Navigate href="/" />
      </Show>

      <Show when={!isDone()}>
        <div class="row">
          <div class="col-2"></div>
          <div class="col-8 is-center">
            {animate()}
          </div>
        </div>
      </Show >
    </>
  )
}
