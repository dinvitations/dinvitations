<style>
  .resizable-wrapper {
    width: 100%;
    overflow: hidden;
    display: flex;
    justify-content: center;
    border: 1px solid #eee;
  }

  .iframe-container {
    width: 480px;
    height: 720px;
    resize: horizontal;
    overflow: auto;
    border: 1px solid #ccc;
    min-width: 480px;
    max-width: 100%;
    box-sizing: border-box;
  }

  .iframe-container iframe {
    width: 100%;
    height: 100%;
    border: none;
  }
</style>

<div class="resizable-wrapper">
  <div class="iframe-container">
    <iframe src="{{ route('invitation.show', ['slug' => $slug]) }}"></iframe>
  </div>
</div>
