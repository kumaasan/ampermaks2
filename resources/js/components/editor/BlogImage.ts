import Image from '@tiptap/extension-image';

export const BlogImage = Image.extend({
    addAttributes() {
        return {
            ...this.parent?.(),
            mediaId: {
                default: null,
                parseHTML: (element) => element.getAttribute('data-media-id'),
                renderHTML: (attributes) =>
                    attributes.mediaId
                        ? { 'data-media-id': attributes.mediaId }
                        : {},
            },
            align: {
                default: 'center',
                parseHTML: (element) =>
                    element.getAttribute('data-align') ?? 'center',
                renderHTML: (attributes) => ({
                    'data-align': attributes.align ?? 'center',
                }),
            },
        };
    },
});
