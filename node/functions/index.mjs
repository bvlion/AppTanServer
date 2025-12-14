import { onRequest } from "firebase-functions/v2/https"
import gplay from 'google-play-scraper'

export const scrapeHandler = async (request, response, client = gplay) => {
  const packageName = request.query.packageName || request.body?.packageName
  if (!packageName) {
    return response.status(400).json({ error: 'Missing Parameter' })
  }

  try {
    const appData = await client.app({
      appId: packageName,
      lang: 'ja',
      country: 'jp',
    })

    response.json({
      title: appData.title,
      description: appData.description,
    })
  } catch (error) {
    response.status(500).json({
      error: 'Failed to fetch from Google Play',
      detail: error.message,
    })
  }
}

export const scrape = onRequest((request, response) => scrapeHandler(request, response))
