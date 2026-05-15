const escapeHtml = (value: string): string => value
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#039;')

export const renderTicketDescription = (description: string | null | undefined): string => {
  if (!description) {
    return ''
  }

  const codeSegments: string[] = []
  let rendered = escapeHtml(description).replace(/`([^`\n]+)`/g, (_, code: string) => {
    const token = `\u0000CODE_SEGMENT_${codeSegments.length}\u0000`
    codeSegments.push(`<code class="ticket-inline-code">${code}</code>`)

    return token
  })

  rendered = rendered
    .replace(/\*\*([^*\n]+)\*\*/g, '<strong>$1</strong>')
    .replace(/&lt;u&gt;([\s\S]+?)&lt;\/u&gt;/g, '<u>$1</u>')
    .replace(/~~([^~\n]+)~~/g, '<s>$1</s>')
    .replace(/\*([^*\n]+)\*/g, '<em>$1</em>')

  codeSegments.forEach((segment, index) => {
    rendered = rendered.replace(`\u0000CODE_SEGMENT_${index}\u0000`, segment)
  })

  return rendered
}
