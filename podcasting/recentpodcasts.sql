SELECT *
FROM podcast_audiofiles pa, podcast_channels pc
WHERE pa.FieldID = 100 AND pa.ChannelID = pc.ChannelID AND pc.FieldID = 1
ORDER BY pa.StringValue DESC LIMIT 20